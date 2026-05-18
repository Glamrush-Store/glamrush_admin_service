<?php

namespace App\Domain\Shipping\ShippingRate\Actions;

use App\Models\ShippingRate;
use Illuminate\Database\Eloquent\Builder;

class BuildShippingRateQueryAction
{
    public function run(array $filters): Builder
    {
        return ShippingRate::query()
            ->with(['zone:id,name', 'method:id,name,code'])
            ->when($filters['shipping_zone_id'] ?? null, fn (Builder $q, $v) => $q->where('shipping_zone_id', $v))
            ->when($filters['shipping_method_id'] ?? null, fn (Builder $q, $v) => $q->where('shipping_method_id', $v))
            ->when($filters['rate_type'] ?? null, fn (Builder $q, $v) => $q->where('rate_type', $v))
            ->when(isset($filters['is_active']), fn (Builder $q) => $q->where('is_active', $filters['is_active']))
            ->latest();
    }
}
