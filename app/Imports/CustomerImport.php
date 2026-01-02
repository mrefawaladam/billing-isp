<?php

namespace App\Imports;

use App\Models\Customer;
use App\Models\Package;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class CustomerImport implements ToModel, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading, SkipsOnFailure, WithStartRow
{
    use SkipsFailures;

    protected $errors = [];
    protected $successCount = 0;
    protected $failCount = 0;
    protected $rowNumber = 0;
    protected $processedRows = 0;

    /**
     * @return int
     */
    public function startRow(): int
    {
        return 23; // Start from row 23 (skip instruction rows 1-22, row 23 is header)
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $this->rowNumber++;
        $this->processedRows++;
        
        // Log raw row data for debugging
        Log::info('Customer Import - Processing row', [
            'row_number' => $this->rowNumber,
            'processed_rows' => $this->processedRows,
            'raw_row' => $row,
            'row_keys' => array_keys($row),
            'row_count' => count($row),
        ]);
        
        try {
            // Check if row is empty
            $isEmpty = true;
            foreach ($row as $value) {
                if (!empty($value) && trim($value) !== '' && trim($value) !== null) {
                    $isEmpty = false;
                    break;
                }
            }
            
            if ($isEmpty) {
                Log::warning('Customer Import - Empty row skipped', [
                    'row_number' => $this->rowNumber,
                ]);
                return null;
            }
            
            // Check if this is a header row (row 23 in template, or first row after startRow)
            // Header rows typically contain column names like "Kode Pelanggan", "Nama", etc.
            $isHeaderRow = false;
            $headerKeywords = ['kode_pelanggan', 'nama', 'no_telepon', 'alamat', 'kabupaten', 'kecamatan'];
            foreach ($row as $key => $value) {
                $normalizedValue = strtolower(trim($value ?? ''));
                if (in_array($normalizedValue, ['kode pelanggan', 'nama', 'no. telepon', 'alamat', 'kabupaten', 'kecamatan'])) {
                    $isHeaderRow = true;
                    break;
                }
            }
            
            if ($isHeaderRow) {
                Log::info('Customer Import - Header row detected, skipping', [
                    'row_number' => $this->rowNumber,
                ]);
                return null;
            }
            
            // Normalize column names (handle different possible header formats)
            $normalizedRow = [];
            $hasNumericKeys = false;
            foreach ($row as $key => $value) {
                if (is_numeric($key)) {
                    $hasNumericKeys = true;
                    break;
                }
            }
            
            // Column mapping for numeric keys (when header is not detected properly)
            $columnMap = [
                0 => 'kode_pelanggan',
                1 => 'nama',
                2 => 'no_telepon',
                3 => 'alamat',
                4 => 'kabupaten',
                5 => 'kecamatan',
                6 => 'kelurahan_desa',
                7 => 'latitude',
                8 => 'longitude',
                9 => 'tipe',
                10 => 'status_aktif',
                11 => 'kode_paket',
                12 => 'nama_paket',
                13 => 'biaya_bulanan',
                14 => 'gunakan_harga_custom',
                15 => 'diskon',
                16 => 'ppn_termasuk',
                17 => 'total_biaya',
                18 => 'tanggal_jatuh_tempo',
                19 => 'penanggung_jawab',
            ];
            
            foreach ($row as $key => $value) {
                // Handle special case: "panduan_import_data_pelangan" should be "kode_pelanggan" (first column, index 0)
                $normalizedKeyCheck = strtolower(trim($key));
                $normalizedKeyCheck = preg_replace('/[^a-z0-9_]/', '_', $normalizedKeyCheck);
                if ($normalizedKeyCheck === 'panduan_import_data_pelangan') {
                    $normalizedRow['kode_pelanggan'] = $value;
                    continue; // Skip adding to normalizedRow again
                }
                
                // Handle numeric keys (when header is not detected properly or using template)
                if (is_numeric($key) || $hasNumericKeys) {
                    $normalizedKey = $columnMap[$key] ?? 'unknown_' . $key;
                } else {
                    $normalizedKey = strtolower(trim($key));
                    // Remove special characters and spaces, keep only alphanumeric and underscore
                    $normalizedKey = preg_replace('/[^a-z0-9_]/', '_', $normalizedKey);
                }
                
                // Convert value to string if it's numeric but should be string (like package_code)
                if (is_numeric($value) && ($normalizedKey === 'kode_paket' || $normalizedKey === 'package_code')) {
                    $value = (string) $value;
                }
                
                $normalizedRow[$normalizedKey] = $value;
            }
            
            Log::info('Customer Import - Normalized row', [
                'row_number' => $this->rowNumber,
                'has_numeric_keys' => $hasNumericKeys,
                'normalized_row' => $normalizedRow,
            ]);
            
            // Find or create package by package_code
            $package = null;
            $packageCode = $normalizedRow['kode_paket'] ?? $normalizedRow['package_code'] ?? null;
            if (!empty($packageCode)) {
                // Convert to string if numeric (Excel sometimes reads as number)
                $packageCode = (string) $packageCode;
                $packageCode = trim($packageCode);
                
                $package = Package::where('package_code', $packageCode)->first();
                if (!$package) {
                    $this->failCount++;
                    $this->errors[] = "Baris " . ($this->rowNumber + 1) . ": Paket dengan kode '{$packageCode}' tidak ditemukan";
                    Log::warning('Customer Import - Package not found', [
                        'row_number' => $this->rowNumber,
                        'package_code' => $packageCode,
                        'normalized_row' => $normalizedRow,
                    ]);
                    return null;
                }
            }

            // Get assigned users by name
            $assignedUserIds = [];
            $penanggungJawab = $normalizedRow['penanggung_jawab'] ?? null;
            if (!empty($penanggungJawab)) {
                $assignedUserNames = explode(',', $penanggungJawab);
                foreach ($assignedUserNames as $userName) {
                    $userName = trim($userName);
                    if (!empty($userName)) {
                        $user = User::where('name', $userName)->first();
                        if ($user) {
                            $assignedUserIds[] = $user->id;
                        }
                    }
                }
            }

            // Determine monthly_fee
            $monthlyFee = 0;
            $useCustomPrice = false;
            $biayaBulanan = $normalizedRow['biaya_bulanan'] ?? $normalizedRow['monthly_fee'] ?? null;
            $gunakanHargaCustom = $normalizedRow['gunakan_harga_custom'] ?? $normalizedRow['use_custom_price'] ?? 'tidak';
            
            if (!empty($biayaBulanan)) {
                $monthlyFee = (float) $biayaBulanan;
                $useCustomPrice = strtolower($gunakanHargaCustom) === 'ya' || strtolower($gunakanHargaCustom) === 'yes' || $gunakanHargaCustom === '1';
            } elseif ($package) {
                $monthlyFee = $package->price;
            }

            // Generate customer_code if empty
            $customerCode = $normalizedRow['kode_pelanggan'] ?? $normalizedRow['customer_code'] ?? null;
            if (empty($customerCode) || trim($customerCode) === '') {
                $customerCode = 'CUST' . strtoupper(Str::random(6));
            } else {
                $customerCode = trim($customerCode);
            }

            // Check if customer_code already exists
            $existingCustomer = Customer::where('customer_code', $customerCode)->first();
            if ($existingCustomer) {
                $this->failCount++;
                $errorMsg = "Baris " . ($this->rowNumber + 1) . ": Kode pelanggan '{$customerCode}' sudah ada";
                $this->errors[] = $errorMsg;
                Log::warning('Customer Import - Duplicate customer code', [
                    'row_number' => $this->rowNumber,
                    'customer_code' => $customerCode,
                ]);
                return null;
            }
            
            // Validate required field: name
            $name = trim($normalizedRow['nama'] ?? $normalizedRow['name'] ?? '');
            if (empty($name)) {
                $this->failCount++;
                $errorMsg = "Baris " . ($this->rowNumber + 1) . ": Nama pelanggan wajib diisi";
                $this->errors[] = $errorMsg;
                Log::warning('Customer Import - Missing required field: name', [
                    'row_number' => $this->rowNumber,
                    'normalized_row' => $normalizedRow,
                ]);
                return null;
            }

            $customer = new Customer([
                'id' => (string) Str::uuid(),
                'customer_code' => $customerCode,
                'name' => $name,
                'phone' => trim($normalizedRow['no_telepon'] ?? $normalizedRow['phone'] ?? '') ?: null,
                'address' => trim($normalizedRow['alamat'] ?? $normalizedRow['address'] ?? '') ?: null,
                'kabupaten' => trim($normalizedRow['kabupaten'] ?? '') ?: null,
                'kecamatan' => trim($normalizedRow['kecamatan'] ?? '') ?: null,
                'kelurahan' => trim($normalizedRow['kelurahan_desa'] ?? $normalizedRow['kelurahan'] ?? '') ?: null,
                'lat' => !empty($normalizedRow['latitude'] ?? $normalizedRow['lat'] ?? null) ? (float) ($normalizedRow['latitude'] ?? $normalizedRow['lat']) : null,
                'lng' => !empty($normalizedRow['longitude'] ?? $normalizedRow['lng'] ?? null) ? (float) ($normalizedRow['longitude'] ?? $normalizedRow['lng']) : null,
                'type' => trim($normalizedRow['tipe'] ?? $normalizedRow['type'] ?? 'Residential') ?: 'Residential',
                'active' => strtolower(trim($normalizedRow['status_aktif'] ?? $normalizedRow['active'] ?? 'aktif')) === 'aktif' || strtolower(trim($normalizedRow['status_aktif'] ?? $normalizedRow['active'] ?? 'aktif')) === 'active',
                'package_id' => $package ? $package->id : null,
                'monthly_fee' => $monthlyFee,
                'use_custom_price' => $useCustomPrice,
                'discount' => !empty($normalizedRow['diskon'] ?? $normalizedRow['discount'] ?? null) ? (float) ($normalizedRow['diskon'] ?? $normalizedRow['discount']) : 0,
                'ppn_included' => strtolower(trim($normalizedRow['ppn_termasuk'] ?? $normalizedRow['ppn_included'] ?? 'ya')) === 'ya' || strtolower(trim($normalizedRow['ppn_termasuk'] ?? $normalizedRow['ppn_included'] ?? 'ya')) === 'yes',
                'total_fee' => !empty($normalizedRow['total_biaya'] ?? $normalizedRow['total_fee'] ?? null) ? (float) ($normalizedRow['total_biaya'] ?? $normalizedRow['total_fee']) : $monthlyFee,
                'invoice_due_day' => !empty($normalizedRow['tanggal_jatuh_tempo'] ?? $normalizedRow['invoice_due_day'] ?? null) ? (int) ($normalizedRow['tanggal_jatuh_tempo'] ?? $normalizedRow['invoice_due_day']) : null,
            ]);

            $customer->save();
            
            Log::info('Customer Import - Customer saved successfully', [
                'row_number' => $this->rowNumber,
                'customer_id' => $customer->id,
                'customer_code' => $customer->customer_code,
                'name' => $customer->name,
            ]);

            // Sync assigned users
            if (!empty($assignedUserIds)) {
                $customer->assignedUsers()->sync($assignedUserIds);
                Log::info('Customer Import - Assigned users synced', [
                    'row_number' => $this->rowNumber,
                    'customer_id' => $customer->id,
                    'assigned_user_ids' => $assignedUserIds,
                ]);
            }

            $this->successCount++;
            return $customer;

        } catch (\Exception $e) {
            $this->failCount++;
            $errorMsg = "Baris " . ($this->rowNumber + 1) . ": " . $e->getMessage();
            $this->errors[] = $errorMsg;
            Log::error('Customer Import Error', [
                'row_number' => $this->rowNumber,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'raw_row' => $row,
                'normalized_row' => $normalizedRow ?? [],
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        // Return empty rules to allow manual validation in model() method
        // This prevents Laravel Excel from rejecting rows before we can process them
        return [];
    }

    /**
     * @return array
     */
    public function customValidationMessages()
    {
        return [
            'nama.required' => 'Nama pelanggan wajib diisi',
            'nama.string' => 'Nama pelanggan harus berupa teks',
        ];
    }

    /**
     * @return int
     */
    public function batchSize(): int
    {
        return 1; // Set to 1 to prevent batch insert duplicate issues (we save immediately in model())
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 100;
    }

    /**
     * Get errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get success count
     */
    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    /**
     * Get fail count
     */
    public function getFailCount(): int
    {
        return $this->failCount;
    }

    /**
     * Get processed rows count
     */
    public function getProcessedRowsCount(): int
    {
        return $this->processedRows;
    }

}

