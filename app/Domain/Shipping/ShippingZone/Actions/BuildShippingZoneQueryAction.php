<?php

namespace App\Domain\Shipping\ShippingZone\Actions;

use App\Models\ShippingZone;
use Illuminate\Database\Eloquent\Builder;

class BuildShippingZoneQueryAction
{
    public function run(array $filters): Builder
    {
        return ShippingZone::query()
            ->when($filters['name'] ?? null, fn (Builder $q, $v) => $q->where('name', 'like', "%{$v}%"))
            ->when($filters['country'] ?? null, fn (Builder $q, $v) => $q->where('country', $v))
            ->when(isset($filters['is_active']), fn (Builder $q) => $q->where('is_active', $filters['is_active']))
            ->latest();
    }
}
