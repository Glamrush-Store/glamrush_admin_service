<?php

namespace App\Models;

use App\Domain\Discount\Enums\DiscountType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiscountCode extends Model
{
    use HasUlids, SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'code', 'name', 'description', 'type', 'value', 'currency',
        'maximum_discount_amount', 'minimum_subtotal', 'starts_at', 'ends_at',
        'is_active', 'total_usage_limit', 'per_customer_usage_limit',
        'first_order_only', 'applies_to_sale_items', 'applies_to_all_storefronts',
        'created_by_admin_id', 'updated_by_admin_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => DiscountType::class,
            'value' => 'decimal:2', 'maximum_discount_amount' => 'decimal:2', 'minimum_subtotal' => 'decimal:2',
            'starts_at' => 'immutable_datetime', 'ends_at' => 'immutable_datetime',
            'is_active' => 'boolean', 'first_order_only' => 'boolean',
            'applies_to_sale_items' => 'boolean', 'applies_to_all_storefronts' => 'boolean',
        ];
    }

    public function storefronts(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'discount_code_storefronts')->withTimestamps();
    }

    public function targets(): HasMany
    {
        return $this->hasMany(DiscountCodeTarget::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_admin_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_admin_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(fn (Builder $q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()));
    }

    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('is_active', true)->where('starts_at', '>', now());
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->whereNotNull('ends_at')->where('ends_at', '<=', now());
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->whereNotNull('starts_at')->where('starts_at', '>', now());
    }

    public function state(): string
    {
        if ($this->ends_at?->isPast()) {
            return 'expired';
        }
        if ($this->is_active && $this->starts_at?->isFuture()) {
            return 'scheduled';
        }
        if ($this->is_active) {
            return 'active';
        }
        if ($this->starts_at === null && $this->ends_at === null) {
            return 'draft';
        }

        return 'inactive';
    }
}
