<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CustomerService
{
    /**
     * Hitung total fee berdasarkan monthly_fee, discount, dan ppn_included
     */
    private function calculateTotalFee(float $monthlyFee, float $discount, bool $ppnIncluded): float
    {
        $amountBeforeTax = $monthlyFee - $discount;

        if ($ppnIncluded) {
            // Jika PPN sudah termasuk, total_fee = monthly_fee - discount
            return $amountBeforeTax;
        } else {
            // Jika PPN belum termasuk, tambahkan PPN 10%
            $taxAmount = $amountBeforeTax * 0.1;
            return $amountBeforeTax + $taxAmount;
        }
    }

    /**
     * Generate customer code jika belum ada
     */
    private function generateCustomerCode(): string
    {
        $lastCustomer = Customer::orderBy('customer_code', 'desc')
            ->whereNotNull('customer_code')
            ->where('customer_code', 'like', 'CUST%')
            ->first();

        if ($lastCustomer) {
            $lastNumber = (int) substr($lastCustomer->customer_code, 4);
            $newNumber = $lastNumber + 1;
            return 'CUST' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
        }

        return 'CUST000001';
    }

    /**
     * Create a new customer
     */
    public function create(array $data, ?UploadedFile $housePhoto = null): Customer
    {
        // Generate customer code jika belum ada
        if (empty($data['customer_code'])) {
            $data['customer_code'] = $this->generateCustomerCode();
        }

        // Handle house photo upload
        if ($housePhoto) {
            $data['house_photo_url'] = $this->uploadHousePhoto($housePhoto);
        }

        // Hitung total fee
        $data['total_fee'] = $this->calculateTotalFee(
            $data['monthly_fee'] ?? 0,
            isset($data['discount']) && $data['discount'] !== '' ? (float)$data['discount'] : 0,
            $data['ppn_included'] ?? false
        );

        // Set discount ke 0 jika null atau empty
        if (!isset($data['discount']) || $data['discount'] === '' || $data['discount'] === null) {
            $data['discount'] = 0;
        }

        // Generate UUID untuk id
        if (empty($data['id'])) {
            $data['id'] = Str::uuid()->toString();
        }

        $customer = Customer::create($data);

        return $customer;
    }

    /**
     * Update customer
     */
    public function update(Customer $customer, array $data, ?UploadedFile $housePhoto = null): Customer
    {
        // Handle house photo upload
        if ($housePhoto) {
            // Delete old photo if exists
            if ($customer->house_photo_url) {
                $this->deleteHousePhoto($customer->house_photo_url);
            }
            $data['house_photo_url'] = $this->uploadHousePhoto($housePhoto);
        }

        // Handle discount - pastikan tidak null dan convert ke float
        if (array_key_exists('discount', $data)) {
            $data['discount'] = (float)($data['discount'] ?? 0);
        }

        // Handle ppn_included - pastikan boolean
        if (array_key_exists('ppn_included', $data)) {
            $data['ppn_included'] = isset($data['ppn_included']) && $data['ppn_included'] !== '0';
        }

        // Handle active - pastikan boolean
        if (array_key_exists('active', $data)) {
            $data['active'] = isset($data['active']) && $data['active'] !== '0';
        }

        // Hitung ulang total fee
        // Gunakan nilai dari data jika ada, jika tidak gunakan nilai yang sudah ada
        $monthlyFee = $data['monthly_fee'] ?? $customer->monthly_fee;
        $discount = $data['discount'] ?? $customer->discount ?? 0;
        $ppnIncluded = $data['ppn_included'] ?? $customer->ppn_included;

        // Selalu hitung ulang total fee untuk memastikan konsistensi
        $data['total_fee'] = $this->calculateTotalFee($monthlyFee, $discount, $ppnIncluded);

        $customer->update($data);

        return $customer->fresh();
    }

    /**
     * Upload house photo
     */
    private function uploadHousePhoto(UploadedFile $file): string
    {
        $filename = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('customers/house-photos', $filename, 'public');
        return Storage::disk('public')->url($path);
    }

    /**
     * Delete house photo
     */
    private function deleteHousePhoto(string $url): bool
    {
        try {
            $path = str_replace(Storage::disk('public')->url(''), '', $url);
            return Storage::disk('public')->delete($path);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Delete customer
     */
    public function delete(Customer $customer): bool
    {
        return $customer->delete();
    }

    /**
     * Get validation rules for create
     */
    public static function getCreateRules(): array
    {
        return [
            'customer_code' => 'nullable|string|max:50|unique:customers,customer_code',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
            'house_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'type' => 'required|in:rumahan,kantor,sekolah,free',
            'active' => 'nullable|boolean',
            'assigned_to' => 'nullable|exists:users,id',
            'monthly_fee' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'ppn_included' => 'nullable|boolean',
            'invoice_due_day' => 'required|integer|min:1|max:31',
        ];
    }

    /**
     * Get validation rules for update
     */
    public static function getUpdateRules(Customer $customer): array
    {
        return [
            'customer_code' => 'nullable|string|max:50|unique:customers,customer_code,' . $customer->id,
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
            'house_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'type' => 'required|in:rumahan,kantor,sekolah,free',
            'active' => 'nullable|boolean',
            'assigned_to' => 'nullable|exists:users,id',
            'monthly_fee' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'ppn_included' => 'nullable|boolean',
            'invoice_due_day' => 'required|integer|min:1|max:31',
        ];
    }
}

