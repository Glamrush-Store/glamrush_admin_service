<?php

namespace App\Domain\Shipping\Shipment\Actions;

use App\Models\Shipment;
use Illuminate\Database\Eloquent\Builder;

class BuildShipmentQueryAction
{
    public function run(array $filters): Builder
    {
        return Shipment::query()
            ->with(['method:id,name,code', 'zone:id,name'])
            ->when($filters['order_id'] ?? null, fn (Builder $q, $v) => $q->where('order_id', $v))
            ->when($filters['status'] ?? null, fn (Builder $q, $v) => $q->where('status', $v))
            ->when($filters['shipping_method_id'] ?? null, fn (Builder $q, $v) => $q->where('shipping_method_id', $v))
            ->when($filters['shipping_zone_id'] ?? null, fn (Builder $q, $v) => $q->where('shipping_zone_id', $v))
            ->latest();
    }
}
