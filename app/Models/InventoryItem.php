<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class InventoryItem extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    protected $fillable = [
        'id',
        'code',
        'name',
        'type',
        'brand',
        'model',
        'description',
        'stock_quantity',
        'min_stock',
        'unit',
        'price',
        'location',
        'active',
    ];

    protected $casts = [
        'stock_quantity' => 'integer',
        'min_stock' => 'integer',
        'price' => 'decimal:2',
        'active' => 'boolean',
    ];

    /**
     * Get all usages for this inventory item
     */
    public function usages(): HasMany
    {
        return $this->hasMany(InventoryUsage::class);
    }

    /**
     * Check if stock is low (stock <= min_stock)
     */
    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->min_stock;
    }

    /**
     * Get available stock (stock that can be used)
     */
    public function getAvailableStock(): int
    {
        return max(0, $this->stock_quantity);
    }
}
