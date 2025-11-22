<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Customer extends Model
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
        'customer_code',
        'name',
        'phone',
        'address',
        'lat',
        'lng',
        'house_photo_url',
        'type',
        'active',
        'assigned_to',
        'monthly_fee',
        'discount',
        'ppn_included',
        'total_fee',
        'invoice_due_day',
    ];

    protected $casts = [
        'active' => 'boolean',
        'ppn_included' => 'boolean',
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
        'monthly_fee' => 'decimal:2',
        'discount' => 'decimal:2',
        'total_fee' => 'decimal:2',
    ];

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}

