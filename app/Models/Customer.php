<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'kabupaten',
        'kecamatan',
        'kelurahan',
        'lat',
        'lng',
        'house_photo_url',
        'identity_photo_url',
        'type',
        'active',
        'monthly_fee',
        'package_id',
        'use_custom_price',
        'discount',
        'ppn_included',
        'total_fee',
        'invoice_due_day',
    ];

    protected $casts = [
        'active' => 'boolean',
        'ppn_included' => 'boolean',
        'use_custom_price' => 'boolean',
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
        'monthly_fee' => 'decimal:2',
        'discount' => 'decimal:2',
        'total_fee' => 'decimal:2',
    ];

    public function assignedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'customer_user', 'customer_id', 'user_id')
            ->withTimestamps();
    }

    /**
     * Legacy method for backward compatibility (returns first assigned user)
     * @deprecated Use assignedUsers() instead
     */
    public function assignedUser(): ?User
    {
        return $this->assignedUsers()->first();
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Get effective monthly fee (from package or custom price)
     */
    public function getEffectiveMonthlyFeeAttribute(): float
    {
        if ($this->use_custom_price) {
            return (float) $this->monthly_fee;
        }
        return $this->package ? (float) $this->package->price : (float) $this->monthly_fee;
    }
}

