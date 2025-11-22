<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Invoice extends Model
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
        'customer_id',
        'year',
        'month',
        'invoice_number',
        'due_date',
        'amount_before_tax',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'status',
        'months_overdue',
        'generated_at',
        'generated_by',
        'paid_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'generated_at' => 'datetime',
        'paid_at' => 'datetime',
        'amount_before_tax' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function waNotifications(): HasMany
    {
        return $this->hasMany(WaNotification::class);
    }
}

