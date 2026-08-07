<?php

namespace App\Models;

use App\Domain\Order\Enums\OrderStatus;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasUlids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'guest_id',
        'order_number',
        'status',
        'subtotal',
        'shipping_amount',
        'total',
        'currency',
        'shipping_rate_id',
        'shipping_method_name',
        'shipping_zone_name',
        'shipping_address',
        'billing_address',
        'placed_at',
        'paid_at',
        'expires_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'shipping_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'status' => OrderStatus::class,
            'shipping_address' => 'array',
            'billing_address' => 'array',
            'placed_at' => 'datetime',
            'paid_at' => 'datetime',
            'expires_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function shippingRate(): BelongsTo
    {
        return $this->belongsTo(ShippingRate::class, 'shipping_rate_id');
    }

    public function shipment(): HasOne
    {
        return $this->hasOne(Shipment::class, 'order_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'order_id');
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class, 'order_id')->latestOfMany();
    }
}

