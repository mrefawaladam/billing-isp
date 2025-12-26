<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Package;
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
    public function create(array $data, ?UploadedFile $housePhoto = null, ?UploadedFile $identityPhoto = null): Customer
    {
        // Generate customer code jika belum ada
        if (empty($data['customer_code'])) {
            $data['customer_code'] = $this->generateCustomerCode();
        }

        // Handle house photo upload
        if ($housePhoto) {
            $data['house_photo_url'] = $this->uploadHousePhoto($housePhoto);
        }

        // Handle identity photo upload
        if ($identityPhoto) {
            $data['identity_photo_url'] = $this->uploadIdentityPhoto($identityPhoto);
        }

        // Handle package_id and monthly_fee
        $useCustomPrice = isset($data['use_custom_price']) && $data['use_custom_price'];
        
        if (!empty($data['package_id']) && !$useCustomPrice) {
            // If package is selected and not using custom price, use package price
            $package = Package::find($data['package_id']);
            if ($package) {
                $data['monthly_fee'] = $package->price;
                $data['use_custom_price'] = false;
            }
        } else {
            // Use custom price from input
            $data['use_custom_price'] = $useCustomPrice;
        }

        // Set default monthly_fee if not set
        if (!isset($data['monthly_fee']) || $data['monthly_fee'] === '') {
            $data['monthly_fee'] = 0;
        }

        // Hitung total fee
        $data['total_fee'] = $this->calculateTotalFee(
            (float) $data['monthly_fee'],
            isset($data['discount']) && $data['discount'] !== '' ? (float)$data['discount'] : 0,
            $data['ppn_included'] ?? false
        );

        // Set discount ke 0 jika null atau empty
        if (!isset($data['discount']) || $data['discount'] === '' || $data['discount'] === null) {
            $data['discount'] = 0;
        }

        // Set use_custom_price default to false if not set
        if (!isset($data['use_custom_price'])) {
            $data['use_custom_price'] = false;
        }

        // Generate UUID untuk id
        if (empty($data['id'])) {
            $data['id'] = Str::uuid()->toString();
        }

        // Handle assigned users (extract before creating customer)
        $assignedUsers = [];
        if (isset($data['assigned_users']) && is_array($data['assigned_users'])) {
            $assignedUsers = array_filter($data['assigned_users']);
            unset($data['assigned_users']); // Remove from data before creating
        } elseif (isset($data['assigned_to'])) {
            // Backward compatibility: single assigned_to
            if (!empty($data['assigned_to'])) {
                $assignedUsers = [$data['assigned_to']];
            }
            unset($data['assigned_to']);
        }

        $customer = Customer::create($data);

        // Sync assigned users
        if (!empty($assignedUsers)) {
            $customer->assignedUsers()->sync($assignedUsers);
        }

        return $customer->fresh()->load('assignedUsers');
    }

    /**
     * Update customer
     */
    public function update(Customer $customer, array $data, ?UploadedFile $housePhoto = null, ?UploadedFile $identityPhoto = null): Customer
    {
        // Handle assigned users FIRST (before modifying $data)
        $assignedUsers = [];
        $hasAssignedUsers = false;
        if (isset($data['assigned_users']) && is_array($data['assigned_users'])) {
            $assignedUsers = array_filter($data['assigned_users']);
            $hasAssignedUsers = true;
            unset($data['assigned_users']); // Remove from data before updating
        } elseif (isset($data['assigned_to'])) {
            // Backward compatibility: single assigned_to
            if (!empty($data['assigned_to'])) {
                $assignedUsers = [$data['assigned_to']];
            } else {
                $assignedUsers = [];
            }
            $hasAssignedUsers = true;
            unset($data['assigned_to']);
        }

        // Handle house photo upload
        if ($housePhoto) {
            // Delete old photo if exists
            if ($customer->house_photo_url) {
                $this->deleteHousePhoto($customer->house_photo_url);
            }
            $data['house_photo_url'] = $this->uploadHousePhoto($housePhoto);
        }

        // Handle identity photo upload
        if ($identityPhoto) {
            // Delete old photo if exists
            if ($customer->identity_photo_url) {
                $this->deleteIdentityPhoto($customer->identity_photo_url);
            }
            $data['identity_photo_url'] = $this->uploadIdentityPhoto($identityPhoto);
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

        // Handle package_id and monthly_fee
        $useCustomPrice = isset($data['use_custom_price']) && $data['use_custom_price'];
        
        if (!empty($data['package_id']) && !$useCustomPrice) {
            // If package is selected and not using custom price, use package price
            $package = Package::find($data['package_id']);
            if ($package) {
                $data['monthly_fee'] = $package->price;
                $data['use_custom_price'] = false;
            }
        } else {
            // Use custom price from input or existing value
            if (array_key_exists('use_custom_price', $data)) {
                $data['use_custom_price'] = $useCustomPrice;
            }
        }

        // Hitung ulang total fee
        // Gunakan nilai dari data jika ada, jika tidak gunakan nilai yang sudah ada
        $monthlyFee = isset($data['monthly_fee']) ? (float) $data['monthly_fee'] : $customer->monthly_fee;
        $discount = $data['discount'] ?? $customer->discount ?? 0;
        $ppnIncluded = $data['ppn_included'] ?? $customer->ppn_included;

        // Selalu hitung ulang total fee untuk memastikan konsistensi
        $data['total_fee'] = $this->calculateTotalFee($monthlyFee, $discount, $ppnIncluded);

        $customer->update($data);

        // Sync assigned users if provided in request
        if ($hasAssignedUsers) {
            $customer->assignedUsers()->sync($assignedUsers);
        }

        return $customer->fresh()->load('assignedUsers');
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
     * Upload identity photo
     */
    private function uploadIdentityPhoto(UploadedFile $file): string
    {
        $filename = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('customers/identity-photos', $filename, 'public');
        return Storage::disk('public')->url($path);
    }

    /**
     * Delete identity photo
     */
    private function deleteIdentityPhoto(string $url): bool
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
            'kabupaten' => 'nullable|string|max:255',
            'kecamatan' => 'nullable|string|max:255',
            'kelurahan' => 'nullable|string|max:255',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
            'house_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'identity_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'type' => 'required|in:rumahan,kantor,sekolah,free',
            'active' => 'nullable|boolean',
            'assigned_users' => 'nullable|array',
            'assigned_users.*' => 'nullable|uuid|exists:users,id',
            'assigned_to' => 'nullable|uuid|exists:users,id', // Backward compatibility
            'package_id' => 'nullable|exists:packages,id',
            'use_custom_price' => 'nullable|boolean',
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
            'kabupaten' => 'nullable|string|max:255',
            'kecamatan' => 'nullable|string|max:255',
            'kelurahan' => 'nullable|string|max:255',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
            'house_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'identity_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'type' => 'required|in:rumahan,kantor,sekolah,free',
            'active' => 'nullable|boolean',
            'assigned_users' => 'nullable|array',
            'assigned_users.*' => 'nullable|uuid|exists:users,id',
            'assigned_to' => 'nullable|uuid|exists:users,id', // Backward compatibility
            'package_id' => 'nullable|exists:packages,id',
            'use_custom_price' => 'nullable|boolean',
            'monthly_fee' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'ppn_included' => 'nullable|boolean',
            'invoice_due_day' => 'required|integer|min:1|max:31',
        ];
    }
}

