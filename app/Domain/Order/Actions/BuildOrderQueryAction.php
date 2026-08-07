<?php

namespace App\Domain\Order\Actions;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;

class BuildOrderQueryAction
{
    public function run(array $filters): Builder
    {
        $sortBy = in_array($filters['sort_by'] ?? 'created_at', [
            'created_at',
            'placed_at',
            'paid_at',
            'order_number',
            'status',
            'total',
        ], true) ? ($filters['sort_by'] ?? 'created_at') : 'created_at';

        $sortDir = strtolower($filters['sort_dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        return Order::query()
            ->with([
                'customer:id,name,email,phone',
                'shipment:id,order_id,status,tracking_number,carrier,shipped_at,delivered_at',
                'latestPayment',
                'latestPayment.paymentMethod:id,name,code',
            ])
            ->when($filters['search'] ?? null, function (Builder $q, string $search) {
                $q->where(function (Builder $query) use ($search) {
                    $query->where('order_number', 'like', "%{$search}%")
                        ->orWhere('guest_id', 'like', "%{$search}%")
                        ->orWhereHas('customer', function (Builder $customer) use ($search) {
                            $customer->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                });
            })
            ->when($filters['order_number'] ?? null, fn (Builder $q, string $v) => $q->where('order_number', 'like', "%{$v}%"))
            ->when($filters['status'] ?? null, fn (Builder $q, string $v) => $q->where('status', $v))
            ->when($filters['user_id'] ?? null, fn (Builder $q, string $v) => $q->where('user_id', $v))
            ->when($filters['guest_id'] ?? null, fn (Builder $q, string $v) => $q->where('guest_id', $v))
            ->when($filters['payment_status'] ?? null, fn (Builder $q, string $v) => $q->whereHas('payments', fn (Builder $payment) => $payment->where('status', $v)))
            ->when($filters['shipping_status'] ?? null, fn (Builder $q, string $v) => $q->whereHas('shipment', fn (Builder $shipment) => $shipment->where('status', $v)))
            ->when($filters['placed_from'] ?? null, fn (Builder $q, string $v) => $q->whereDate('placed_at', '>=', $v))
            ->when($filters['placed_to'] ?? null, fn (Builder $q, string $v) => $q->whereDate('placed_at', '<=', $v))
            ->orderBy($sortBy, $sortDir);
    }
}
