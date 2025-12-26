<?php

namespace App\Services;

use App\Models\Package;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PackageService
{
    /**
     * Create a new package
     */
    public function create(array $data): Package
    {
        return DB::transaction(function () use ($data) {
            // Generate UUID untuk id
            if (empty($data['id'])) {
                $data['id'] = Str::uuid()->toString();
            }

            // Set default values
            $data['active'] = $data['active'] ?? true;
            $data['sort_order'] = $data['sort_order'] ?? 0;

            $package = Package::create($data);
            return $package;
        });
    }

    /**
     * Update package
     */
    public function update(Package $package, array $data): Package
    {
        return DB::transaction(function () use ($package, $data) {
            $package->update($data);
            return $package->fresh();
        });
    }

    /**
     * Delete package (soft delete)
     */
    public function delete(Package $package): bool
    {
        return $package->delete();
    }

    /**
     * Get active packages
     */
    public function getActivePackages()
    {
        return Package::where('active', true)
            ->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->get();
    }

    /**
     * Get validation rules for creating package
     */
    public static function getCreateRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'package_code' => 'required|string|max:50|unique:packages,package_code',
            'speed' => 'nullable|string|max:50',
            'service_type' => 'required|in:Dedicated Internet,Internet Broadband,Wireless,Fiber',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ];
    }

    /**
     * Get validation rules for updating package
     */
    public static function getUpdateRules(Package $package): array
    {
        return [
            'name' => 'required|string|max:255',
            'package_code' => 'required|string|max:50|unique:packages,package_code,' . $package->id,
            'speed' => 'nullable|string|max:50',
            'service_type' => 'required|in:Dedicated Internet,Internet Broadband,Wireless,Fiber',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ];
    }
}

