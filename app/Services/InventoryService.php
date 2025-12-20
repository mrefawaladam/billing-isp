<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\InventoryUsage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InventoryService
{
    /**
     * Generate inventory item code
     */
    private function generateItemCode(string $type): string
    {
        $prefix = strtoupper(substr($type, 0, 3)) . '-';
        
        $lastItem = InventoryItem::where('code', 'like', $prefix . '%')
            ->orderBy('code', 'desc')
            ->first();

        if ($lastItem) {
            $lastNumber = (int) substr($lastItem->code, strlen($prefix));
            $newNumber = $lastNumber + 1;
            return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
        }

        return $prefix . '0001';
    }

    /**
     * Create a new inventory item
     */
    public function create(array $data): InventoryItem
    {
        // Generate code jika belum ada
        if (empty($data['code'])) {
            $data['code'] = $this->generateItemCode($data['type'] ?? 'ITEM');
        }

        // Set default values
        $data['stock_quantity'] = $data['stock_quantity'] ?? 0;
        $data['min_stock'] = $data['min_stock'] ?? 0;
        $data['unit'] = $data['unit'] ?? 'pcs';
        $data['price'] = $data['price'] ?? 0;
        $data['active'] = $data['active'] ?? true;

        // Generate UUID untuk id
        if (empty($data['id'])) {
            $data['id'] = Str::uuid()->toString();
        }

        return InventoryItem::create($data);
    }

    /**
     * Update inventory item
     */
    public function update(InventoryItem $item, array $data): InventoryItem
    {
        $item->update($data);
        return $item->fresh();
    }

    /**
     * Delete inventory item
     */
    public function delete(InventoryItem $item): bool
    {
        return $item->delete();
    }

    /**
     * Add stock (restock)
     */
    public function addStock(InventoryItem $item, int $quantity, ?string $notes = null): InventoryItem
    {
        DB::beginTransaction();
        try {
            $item->stock_quantity += $quantity;
            $item->save();

            // Record usage as restock
            InventoryUsage::create([
                'id' => Str::uuid()->toString(),
                'inventory_item_id' => $item->id,
                'quantity' => $quantity,
                'usage_type' => 'restock',
                'used_by' => auth()->id(),
                'used_at' => now(),
                'notes' => $notes ?? 'Restock inventory',
            ]);

            DB::commit();
            return $item->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Use inventory item (reduce stock)
     */
    public function useItem(
        InventoryItem $item,
        int $quantity,
        string $usageType,
        ?string $customerId = null,
        ?string $deviceId = null,
        ?string $notes = null
    ): InventoryUsage {
        DB::beginTransaction();
        try {
            // Check if stock is available
            if ($item->stock_quantity < $quantity) {
                throw new \Exception("Stock tidak mencukupi. Stok tersedia: {$item->stock_quantity}, dibutuhkan: {$quantity}");
            }

            // Reduce stock
            $item->stock_quantity -= $quantity;
            $item->save();

            // Record usage
            $usage = InventoryUsage::create([
                'id' => Str::uuid()->toString(),
                'inventory_item_id' => $item->id,
                'customer_id' => $customerId,
                'device_id' => $deviceId,
                'quantity' => $quantity,
                'usage_type' => $usageType,
                'used_by' => auth()->id(),
                'used_at' => now(),
                'notes' => $notes,
            ]);

            DB::commit();
            return $usage->load('inventoryItem', 'customer', 'device', 'usedBy');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Return item (add stock back)
     */
    public function returnItem(
        InventoryUsage $usage,
        int $quantity,
        ?string $notes = null
    ): InventoryItem {
        DB::beginTransaction();
        try {
            $item = $usage->inventoryItem;
            
            // Add stock back
            $item->stock_quantity += $quantity;
            $item->save();

            // Record return
            InventoryUsage::create([
                'id' => Str::uuid()->toString(),
                'inventory_item_id' => $item->id,
                'customer_id' => $usage->customer_id,
                'device_id' => $usage->device_id,
                'quantity' => $quantity,
                'usage_type' => 'returned',
                'used_by' => auth()->id(),
                'used_at' => now(),
                'notes' => $notes ?? 'Return item',
            ]);

            DB::commit();
            return $item->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get items with low stock (stock <= min_stock)
     */
    public function getLowStockItems(): \Illuminate\Database\Eloquent\Collection
    {
        return InventoryItem::where('active', true)
            ->whereRaw('stock_quantity <= min_stock')
            ->orderBy('stock_quantity', 'asc')
            ->get();
    }

    /**
     * Get items that need restock (stock = 0)
     */
    public function getOutOfStockItems(): \Illuminate\Database\Eloquent\Collection
    {
        return InventoryItem::where('active', true)
            ->where('stock_quantity', 0)
            ->orderBy('name', 'asc')
            ->get();
    }

    /**
     * Get usage history for an item
     */
    public function getUsageHistory(InventoryItem $item, int $limit = 50)
    {
        return InventoryUsage::where('inventory_item_id', $item->id)
            ->with(['customer', 'device', 'usedBy'])
            ->orderBy('used_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get usage history for a customer
     */
    public function getCustomerUsageHistory(string $customerId, int $limit = 50)
    {
        return InventoryUsage::where('customer_id', $customerId)
            ->with(['inventoryItem', 'device', 'usedBy'])
            ->orderBy('used_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get validation rules for creating inventory item
     */
    public static function getCreateRules(): array
    {
        return [
            'code' => 'nullable|string|max:50|unique:inventory_items,code',
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:50',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'stock_quantity' => 'nullable|integer|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'unit' => 'nullable|string|max:20',
            'price' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'active' => 'nullable|boolean',
        ];
    }

    /**
     * Get validation rules for updating inventory item
     */
    public static function getUpdateRules(InventoryItem $item): array
    {
        return [
            'code' => 'nullable|string|max:50|unique:inventory_items,code,' . $item->id,
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:50',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'stock_quantity' => 'nullable|integer|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'unit' => 'nullable|string|max:20',
            'price' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'active' => 'nullable|boolean',
        ];
    }

    /**
     * Get validation rules for using inventory
     */
    public static function getUseItemRules(): array
    {
        return [
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'quantity' => 'required|integer|min:1',
            'usage_type' => 'required|in:installed,maintenance,damaged,lost',
            'customer_id' => 'nullable|exists:customers,id',
            'device_id' => 'nullable|exists:devices,id',
            'notes' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get validation rules for restocking
     */
    public static function getRestockRules(): array
    {
        return [
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ];
    }
}

