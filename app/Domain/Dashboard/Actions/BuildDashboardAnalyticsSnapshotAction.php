<?php

namespace App\Domain\Dashboard\Actions;

use App\Models\DashboardAnalyticsSnapshot;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BuildDashboardAnalyticsSnapshotAction
{
    public function run(string $period = 'week', ?string $from = null, ?string $to = null): DashboardAnalyticsSnapshot
    {
        [$startsAt, $endsAt, $periodKey] = $this->resolveRange($period, $from, $to);

        $payload = $this->buildPayload($periodKey, $startsAt, $endsAt);

        return DashboardAnalyticsSnapshot::query()->updateOrCreate(
            [
                'period' => $periodKey,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
            ],
            [
                'payload' => $payload,
                'aggregated_at' => now(),
            ]
        );
    }

    public function buildTransient(string $period = 'week', ?string $from = null, ?string $to = null): array
    {
        [$startsAt, $endsAt, $periodKey] = $this->resolveRange($period, $from, $to);

        return $this->buildPayload($periodKey, $startsAt, $endsAt);
    }

    public function resolveRange(string $period = 'week', ?string $from = null, ?string $to = null): array
    {
        if ($from !== null || $to !== null) {
            $startsAt = $from ? CarbonImmutable::parse($from)->startOfDay() : now()->subWeek()->toImmutable()->startOfDay();
            $endsAt = $to ? CarbonImmutable::parse($to)->endOfDay() : now()->toImmutable()->endOfDay();

            return [$startsAt, $endsAt, 'custom'];
        }

        return match ($period) {
            'today' => [now()->toImmutable()->startOfDay(), now()->toImmutable()->endOfDay(), 'today'],
            'month' => [now()->toImmutable()->startOfMonth(), now()->toImmutable()->endOfMonth(), 'month'],
            'quarter' => [now()->toImmutable()->startOfQuarter(), now()->toImmutable()->endOfQuarter(), 'quarter'],
            'year' => [now()->toImmutable()->startOfYear(), now()->toImmutable()->endOfYear(), 'year'],
            default => [now()->toImmutable()->startOfWeek(), now()->toImmutable()->endOfWeek(), 'week'],
        };
    }

    private function buildPayload(string $period, CarbonImmutable $startsAt, CarbonImmutable $endsAt): array
    {
        $salesStatuses = config('dashboard.sales_statuses', ['paid', 'processing', 'shipped', 'completed']);
        $completedStatus = config('dashboard.completed_status', 'completed');
        $recentLimit = (int) config('dashboard.limits.recent_orders', 10);
        $productLimit = (int) config('dashboard.limits.products', 10);
        $stockLimit = (int) config('dashboard.limits.stock_alerts', 20);
        $lowStockThreshold = (int) config('dashboard.stock.low_stock_threshold', 2);

        $salesOrders = Order::query()
            ->whereIn('status', $salesStatuses)
            ->whereBetween('created_at', [$startsAt, $endsAt]);

        $lifetimeSales = (float) Order::query()
            ->whereIn('status', $salesStatuses)
            ->sum('total');

        $periodSales = (float) (clone $salesOrders)->sum('total');
        $periodSalesCount = (int) (clone $salesOrders)->count();
        $averageOrderValue = $periodSalesCount > 0 ? round($periodSales / $periodSalesCount, 2) : 0.0;

        $completedOrders = Order::query()
            ->where('status', $completedStatus)
            ->whereBetween('created_at', [$startsAt, $endsAt])
            ->get(['id', 'created_at', 'total']);

        $firstCustomerPurchases = DB::table('orders')
            ->select('user_id', DB::raw('MIN(created_at) as first_purchase_at'))
            ->whereNotNull('user_id')
            ->whereIn('status', $salesStatuses)
            ->groupBy('user_id')
            ->havingRaw('MIN(created_at) between ? and ?', [$startsAt, $endsAt])
            ->get();

        $recentOrders = Order::query()
            ->with(['customer:id,name,email', 'latestPayment'])
            ->latest('created_at')
            ->limit($recentLimit)
            ->get()
            ->map(fn (Order $order) => $this->formatOrder($order))
            ->values()
            ->all();

        $uncompletedOrdersQuery = Order::query()
            ->with(['customer:id,name,email', 'latestPayment'])
            ->where(function ($query) use ($completedStatus) {
                $query->where('status', '!=', $completedStatus)
                    ->orWhereDoesntHave('payments', fn ($payment) => $payment->where('status', 'paid'));
            });

        $uncompletedOrders = (clone $uncompletedOrdersQuery)
            ->latest('created_at')
            ->limit($recentLimit)
            ->get()
            ->map(fn (Order $order) => $this->formatOrder($order))
            ->values()
            ->all();

        return [
            'period' => [
                'key' => $period,
                'starts_at' => $startsAt->toIso8601String(),
                'ends_at' => $endsAt->toIso8601String(),
                'granularity' => $this->granularityFor($period, $startsAt, $endsAt),
            ],
            'currency' => 'NGN',
            'summary' => [
                'lifetime_sales' => round($lifetimeSales, 2),
                'period_sales' => round($periodSales, 2),
                'period_sales_count' => $periodSalesCount,
                'average_order_value' => $averageOrderValue,
                'completed_orders_count' => $completedOrders->count(),
                'new_customer_purchases_count' => $firstCustomerPurchases->count(),
                'uncompleted_or_unpaid_orders_count' => (clone $uncompletedOrdersQuery)->count(),
                'pending_fulfillment_count' => Order::query()->whereIn('status', ['paid', 'processing'])->count(),
                'cancelled_orders_count' => Order::query()->whereBetween('created_at', [$startsAt, $endsAt])->where('status', 'cancelled')->count(),
                'refunded_orders_count' => Order::query()->whereBetween('created_at', [$startsAt, $endsAt])->where('status', 'refunded')->count(),
            ],
            'charts' => [
                'completed_sales' => $this->buildSalesSeries($completedOrders, $period, $startsAt, $endsAt),
                'new_customer_purchases' => $this->buildNewCustomerSeries($firstCustomerPurchases, $period, $startsAt, $endsAt),
            ],
            'recent_orders' => $recentOrders,
            'uncompleted_or_unpaid_orders' => $uncompletedOrders,
            'most_sold_products' => $this->soldProducts($startsAt, $endsAt, $salesStatuses, $productLimit, 'desc'),
            'least_sold_products' => $this->leastSoldProducts($startsAt, $endsAt, $salesStatuses, $productLimit),
            'stock' => $this->stockStatus($lowStockThreshold, $stockLimit),
            'breakdowns' => [
                'orders_by_status' => $this->orderStatusBreakdown($startsAt, $endsAt),
                'payments_by_status' => $this->paymentStatusBreakdown($startsAt, $endsAt),
            ],
            'generated_at' => now()->toIso8601String(),
        ];
    }

    private function buildSalesSeries(Collection $orders, string $period, CarbonImmutable $startsAt, CarbonImmutable $endsAt): array
    {
        $granularity = $this->granularityFor($period, $startsAt, $endsAt);
        $buckets = $this->emptyBuckets($granularity, $startsAt, $endsAt);

        foreach ($orders as $order) {
            $key = $this->bucketKey(CarbonImmutable::parse($order->created_at), $granularity);
            if (! isset($buckets[$key])) {
                continue;
            }

            $buckets[$key]['orders_count']++;
            $buckets[$key]['sales'] = round($buckets[$key]['sales'] + (float) $order->total, 2);
        }

        return array_values($buckets);
    }

    private function buildNewCustomerSeries(Collection $purchases, string $period, CarbonImmutable $startsAt, CarbonImmutable $endsAt): array
    {
        $granularity = $this->granularityFor($period, $startsAt, $endsAt);
        $buckets = $this->emptyBuckets($granularity, $startsAt, $endsAt, ['customers_count' => 0]);

        foreach ($purchases as $purchase) {
            $key = $this->bucketKey(CarbonImmutable::parse($purchase->first_purchase_at), $granularity);
            if (! isset($buckets[$key])) {
                continue;
            }

            $buckets[$key]['customers_count']++;
        }

        return array_values($buckets);
    }

    private function emptyBuckets(string $granularity, CarbonImmutable $startsAt, CarbonImmutable $endsAt, array $extra = []): array
    {
        $step = match ($granularity) {
            'hour' => '1 hour',
            'week' => '1 week',
            'month' => '1 month',
            default => '1 day',
        };

        $rangeStart = match ($granularity) {
            'hour' => $startsAt->startOfHour(),
            'week' => $startsAt->startOfWeek(),
            'month' => $startsAt->startOfMonth(),
            default => $startsAt->startOfDay(),
        };

        $rangeEnd = match ($granularity) {
            'hour' => $endsAt->startOfHour(),
            'week' => $endsAt->startOfWeek(),
            'month' => $endsAt->startOfMonth(),
            default => $endsAt->startOfDay(),
        };

        $buckets = [];
        foreach (CarbonPeriod::create($rangeStart, $step, $rangeEnd) as $date) {
            $date = CarbonImmutable::parse($date);
            $key = $this->bucketKey($date, $granularity);
            $buckets[$key] = array_merge([
                'key' => $key,
                'label' => $this->bucketLabel($date, $granularity),
                'sales' => 0.0,
                'orders_count' => 0,
            ], $extra);
        }

        return $buckets;
    }

    private function bucketKey(CarbonImmutable $date, string $granularity): string
    {
        return match ($granularity) {
            'hour' => $date->format('Y-m-d H:00'),
            'week' => $date->startOfWeek()->format('Y-m-d'),
            'month' => $date->format('Y-m'),
            default => $date->format('Y-m-d'),
        };
    }

    private function bucketLabel(CarbonImmutable $date, string $granularity): string
    {
        return match ($granularity) {
            'hour' => $date->format('g A'),
            'week' => 'Week of '.$date->format('M j'),
            'month' => $date->format('M Y'),
            default => $date->format('M j'),
        };
    }

    private function granularityFor(string $period, CarbonImmutable $startsAt, CarbonImmutable $endsAt): string
    {
        if ($period === 'today') {
            return 'hour';
        }

        if (in_array($period, ['quarter'], true)) {
            return 'week';
        }

        if ($period === 'year') {
            return 'month';
        }

        if ($period === 'custom' && $startsAt->diffInDays($endsAt) > 95) {
            return 'month';
        }

        if ($period === 'custom' && $startsAt->diffInDays($endsAt) > 35) {
            return 'week';
        }

        return 'day';
    }

    private function soldProducts(CarbonImmutable $startsAt, CarbonImmutable $endsAt, array $salesStatuses, int $limit, string $direction): array
    {
        return OrderItem::query()
            ->select([
                'order_items.product_id',
                'order_items.product_variant_id',
                'order_items.product_name',
                'order_items.sku',
                DB::raw('SUM(order_items.quantity) as quantity_sold'),
                DB::raw('SUM(order_items.line_total) as sales_total'),
            ])
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereIn('orders.status', $salesStatuses)
            ->whereBetween('orders.created_at', [$startsAt, $endsAt])
            ->groupBy('order_items.product_id', 'order_items.product_variant_id', 'order_items.product_name', 'order_items.sku')
            ->orderBy('quantity_sold', $direction)
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'product_id' => $row->product_id,
                'product_variant_id' => $row->product_variant_id,
                'name' => $row->product_name,
                'sku' => $row->sku,
                'quantity_sold' => (int) $row->quantity_sold,
                'sales_total' => round((float) $row->sales_total, 2),
            ])
            ->values()
            ->all();
    }

    private function leastSoldProducts(CarbonImmutable $startsAt, CarbonImmutable $endsAt, array $salesStatuses, int $limit): array
    {
        return DB::table('product_variants')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->leftJoin('order_items', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->leftJoin('orders', function ($join) use ($startsAt, $endsAt, $salesStatuses) {
                $join->on('orders.id', '=', 'order_items.order_id')
                    ->whereIn('orders.status', $salesStatuses)
                    ->whereBetween('orders.created_at', [$startsAt, $endsAt]);
            })
            ->whereNull('products.deleted_at')
            ->select([
                'products.id as product_id',
                'product_variants.id as product_variant_id',
                'products.name as product_name',
                'product_variants.sku',
                DB::raw('COALESCE(SUM(CASE WHEN orders.id IS NULL THEN 0 ELSE order_items.quantity END), 0) as quantity_sold'),
                DB::raw('COALESCE(SUM(CASE WHEN orders.id IS NULL THEN 0 ELSE order_items.line_total END), 0) as sales_total'),
            ])
            ->groupBy('products.id', 'product_variants.id', 'products.name', 'product_variants.sku')
            ->orderBy('quantity_sold')
            ->orderBy('products.name')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'product_id' => $row->product_id,
                'product_variant_id' => $row->product_variant_id,
                'name' => $row->product_name,
                'sku' => $row->sku,
                'quantity_sold' => (int) $row->quantity_sold,
                'sales_total' => round((float) $row->sales_total, 2),
            ])
            ->values()
            ->all();
    }

    private function stockStatus(int $threshold, int $limit): array
    {
        $variantStock = ProductVariant::query()->where('manage_stock', true);
        $productStock = Product::query()->where('manage_stock', true);

        $alerts = ProductVariant::query()
            ->with('product:id,name')
            ->where('manage_stock', true)
            ->where(function ($query) use ($threshold) {
                $query->where('stock_quantity', '<', $threshold)
                    ->orWhere('in_stock', false);
            })
            ->orderBy('stock_quantity')
            ->limit($limit)
            ->get()
            ->map(fn (ProductVariant $variant) => [
                'product_id' => $variant->product_id,
                'product_variant_id' => $variant->id,
                'name' => $variant->product?->name,
                'sku' => $variant->sku,
                'stock_quantity' => (int) $variant->stock_quantity,
                'reserved_quantity' => (int) $variant->reserved_quantity,
                'available_quantity' => max(0, (int) $variant->stock_quantity - (int) $variant->reserved_quantity),
                'in_stock' => (bool) $variant->in_stock,
            ])
            ->values()
            ->all();

        return [
            'low_stock_threshold' => $threshold,
            'variants_managed' => (clone $variantStock)->count(),
            'variants_low_stock' => (clone $variantStock)->where('stock_quantity', '<', $threshold)->count(),
            'variants_out_of_stock' => (clone $variantStock)->where(function ($query) {
                $query->where('stock_quantity', '<=', 0)->orWhere('in_stock', false);
            })->count(),
            'products_managed' => (clone $productStock)->count(),
            'products_low_stock' => (clone $productStock)->where('stock_quantity', '<', $threshold)->count(),
            'alerts' => $alerts,
        ];
    }

    private function orderStatusBreakdown(CarbonImmutable $startsAt, CarbonImmutable $endsAt): array
    {
        return DB::table('orders')
            ->select('status', DB::raw('COUNT(*) as total'))
            ->whereBetween('created_at', [$startsAt, $endsAt])
            ->groupBy('status')
            ->orderBy('status')
            ->get()
            ->map(fn ($row) => ['status' => $row->status, 'total' => (int) $row->total])
            ->values()
            ->all();
    }

    private function paymentStatusBreakdown(CarbonImmutable $startsAt, CarbonImmutable $endsAt): array
    {
        return DB::table('payments')
            ->select('status', DB::raw('COUNT(*) as total'))
            ->whereBetween('created_at', [$startsAt, $endsAt])
            ->groupBy('status')
            ->orderBy('status')
            ->get()
            ->map(fn ($row) => ['status' => $row->status, 'total' => (int) $row->total])
            ->values()
            ->all();
    }

    private function formatOrder(Order $order): array
    {
        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status instanceof \App\Domain\Order\Enums\OrderStatus ? $order->status->value : $order->status,
            'payment_status' => $order->latestPayment?->status,
            'customer' => [
                'id' => $order->customer?->id,
                'name' => $order->shipping_address['full_name'] ?? $order->customer?->name,
                'email' => $order->shipping_address['email'] ?? $order->customer?->email,
            ],
            'currency' => $order->currency,
            'total' => round((float) $order->total, 2),
            'placed_at' => $order->placed_at?->toIso8601String(),
            'created_at' => $order->created_at?->toIso8601String(),
        ];
    }
}




