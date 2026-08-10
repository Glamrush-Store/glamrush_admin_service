<?php

namespace App\Domain\Order\UseCases;

use App\Domain\Shared\Actions\CreateAppLogAction;
use App\Exceptions\BusinessException;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\ProductVariant;
use App\Models\ShippingMethod;
use App\Models\ShippingRate;
use App\Models\ShippingZone;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateManualOrderUseCase
{
    public function __construct(private CreateAppLogAction $log) {}

    /** @return array{order: Order, replayed: bool} */
    public function run(array $data, User $actor): array
    {
        $owner = 'admin:'.$actor->getKey();
        $hash = $this->requestHash($data);

        return DB::transaction(function () use ($data, $actor, $owner, $hash): array {
            $existing = Order::query()
                ->where('idempotency_owner', $owner)
                ->where('idempotency_key', $data['idempotency_key'])
                ->lockForUpdate()
                ->first();

            if ($existing) {
                if (! hash_equals((string) $existing->idempotency_request_hash, $hash)) {
                    throw new BusinessException('This idempotency key has already been used with different order data.', [], 409);
                }

                return ['order' => $this->loadOrder($existing), 'replayed' => true];
            }

            $paymentMethod = PaymentMethod::query()->where('is_active', true)->lockForUpdate()->findOrFail($data['payment_method_id']);

            if (isset($data['transaction_reference']) && Payment::query()
                ->where('provider', $paymentMethod->code)
                ->where('transaction_id', $data['transaction_reference'])
                ->exists()) {
                throw new BusinessException('This payment transaction reference has already been recorded.', [], 409);
            }

            $shippingMethod = ShippingMethod::query()->where('is_active', true)->lockForUpdate()->findOrFail($data['shipping_method_id']);
            $shippingZone = ShippingZone::query()->where('is_active', true)->lockForUpdate()->findOrFail($data['shipping_zone_id']);
            $shippingRate = isset($data['shipping_rate_id'])
                ? ShippingRate::query()->where('is_active', true)->lockForUpdate()->findOrFail($data['shipping_rate_id'])
                : null;

            if ($shippingRate && ($shippingRate->shipping_method_id !== $shippingMethod->id || $shippingRate->shipping_zone_id !== $shippingZone->id)) {
                throw new BusinessException('The shipping rate does not match the selected method and zone.', [], 409);
            }

            $requestedItems = collect($data['items'])->keyBy('product_variant_id');
            $variants = ProductVariant::query()
                ->with('product')
                ->whereIn('id', $requestedItems->keys())
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($variants->count() !== $requestedItems->count()) {
                throw new BusinessException('One or more selected product variants no longer exist.', [], 409);
            }

            $itemRows = [];
            $subtotalMinor = 0;

            foreach ($variants as $variant) {
                $input = $requestedItems[$variant->id];
                $product = $variant->product;

                if (! $product || $product->trashed() || $product->status !== 'published' || $variant->status !== 'active') {
                    throw new BusinessException("Variant {$variant->sku} is not available for sale.", [], 409);
                }

                $quantity = (int) $input['quantity'];
                if ($variant->manage_stock) {
                    $available = max(0, (int) $variant->stock_quantity - (int) $variant->reserved_quantity);
                    if ($available < $quantity) {
                        throw new BusinessException("Insufficient available stock for {$variant->sku}. Available: {$available}.", [
                            'product_variant_id' => $variant->id,
                            'available_quantity' => $available,
                        ], 409);
                    }

                    $remaining = (int) $variant->stock_quantity - $quantity;
                    $variant->update(['stock_quantity' => $remaining, 'in_stock' => $remaining > 0]);
                } elseif (! $variant->in_stock) {
                    throw new BusinessException("Variant {$variant->sku} is out of stock.", [], 409);
                }

                $unitMinor = $this->moneyToMinor($input['unit_price']);
                $lineMinor = $unitMinor * $quantity;
                $subtotalMinor += $lineMinor;
                $itemRows[] = [
                    'product_id' => $product->id,
                    'product_variant_id' => $variant->id,
                    'product_name' => $product->name,
                    'product_slug' => $product->slug,
                    'sku' => $variant->sku,
                    'unit_price' => $this->minorToMoney($unitMinor),
                    'quantity' => $quantity,
                    'line_subtotal' => $this->minorToMoney($lineMinor),
                    'discount_amount' => '0.00',
                    'line_total' => $this->minorToMoney($lineMinor),
                    'product_snapshot' => [
                        'product_id' => $product->id,
                        'variant_id' => $variant->id,
                        'name' => $product->name,
                        'slug' => $product->slug,
                        'sku' => $variant->sku,
                        'attributes' => $variant->attributes ?? [],
                        'images' => [],
                    ],
                ];
            }

            $shippingMinor = $this->moneyToMinor($data['shipping_amount'] ?? $shippingRate?->amount ?? 0);
            $totalMinor = $subtotalMinor + $shippingMinor;
            $placedAt = isset($data['placed_at']) ? CarbonImmutable::parse($data['placed_at']) : CarbonImmutable::now();
            $orderStatus = $data['order_status'] ?? 'completed';
            $shipmentStatus = $data['shipment_status'] ?? 'delivered';

            $order = Order::create([
                'user_id' => $data['customer_id'] ?? null,
                'guest_id' => isset($data['customer_id']) ? null : 'manual-'.Str::lower((string) Str::ulid()),
                'idempotency_owner' => $owner,
                'idempotency_key' => $data['idempotency_key'],
                'idempotency_request_hash' => $hash,
                'order_number' => $this->generateOrderNumber(),
                'status' => $orderStatus,
                'subtotal' => $this->minorToMoney($subtotalMinor),
                'discount_amount' => '0.00',
                'shipping_amount' => $this->minorToMoney($shippingMinor),
                'shipping_discount_amount' => '0.00',
                'total' => $this->minorToMoney($totalMinor),
                'currency' => $data['currency'],
                'shipping_rate_id' => $shippingRate?->id,
                'shipping_method_name' => $shippingMethod->name,
                'shipping_zone_name' => $shippingZone->name,
                'shipping_address' => $data['shipping_address'],
                'billing_address' => $data['billing_address'] ?? null,
                'placed_at' => $placedAt,
                'paid_at' => $placedAt,
                'inventory_committed_at' => now(),
            ]);

            $order->items()->createMany($itemRows);

            $payment = $order->payments()->create([
                'idempotency_owner' => $owner,
                'idempotency_key' => 'manual-order:'.$order->id,
                'idempotency_request_hash' => $hash,
                'payment_method_id' => $paymentMethod->id,
                'provider' => $paymentMethod->code,
                'reference' => 'MANUAL-PAY-'.Str::upper((string) Str::ulid()),
                'provider_reference' => $data['transaction_reference'] ?? null,
                'transaction_id' => $data['transaction_reference'] ?? null,
                'amount' => $this->minorToMoney($totalMinor),
                'currency' => $data['currency'],
                'status' => 'paid',
                'paid_at' => $placedAt,
                'metadata' => ['source' => 'manual_admin'],
            ]);

            $payment->transactions()->create([
                'event_key' => 'manual-payment:'.$payment->id.':capture',
                'type' => 'capture',
                'status' => 'paid',
                'provider_reference' => $data['transaction_reference'] ?? null,
                'amount' => $this->minorToMoney($totalMinor),
                'currency' => $data['currency'],
                'payload' => ['source' => 'manual_admin'],
            ]);

            $shippedAt = in_array($shipmentStatus, ['shipped', 'delivered'], true) ? $placedAt : null;
            $order->shipment()->create([
                'shipping_method_id' => $shippingMethod->id,
                'shipping_zone_id' => $shippingZone->id,
                'shipping_amount' => $this->minorToMoney($shippingMinor),
                'status' => $shipmentStatus,
                'tracking_number' => $data['tracking_number'] ?? null,
                'carrier' => $data['carrier'] ?? null,
                'shipped_at' => $shippedAt,
                'delivered_at' => $shipmentStatus === 'delivered' ? $placedAt : null,
            ]);

            $this->log->run('info', 'MANUAL_ORDER_CREATED', 'Manual order created', [
                'order_id' => $order->id,
                'payment_method_id' => $paymentMethod->id,
                'shipping_method_id' => $shippingMethod->id,
                'shipping_zone_id' => $shippingZone->id,
                'line_count' => count($itemRows),
                'changed_fields' => ['order', 'items', 'payment', 'shipment', 'inventory'],
            ], $actor);

            return ['order' => $this->loadOrder($order), 'replayed' => false];
        }, 3);
    }

    private function loadOrder(Order $order): Order
    {
        return $order->load([
            'customer:id,name,email,phone,email_verified_at,created_at,updated_at',
            'items', 'shippingRate.method:id,name,code', 'shippingRate.zone:id,name',
            'shipment.method:id,name,code', 'shipment.zone:id,name',
            'latestPayment.paymentMethod:id,name,code,description', 'latestPayment.transactions',
        ]);
    }

    private function requestHash(array $data): string
    {
        $canonicalize = function (array $value) use (&$canonicalize): array {
            if (! array_is_list($value)) {
                ksort($value);
            }
            foreach ($value as $key => $item) {
                if (is_array($item)) {
                    $value[$key] = $canonicalize($item);
                }
            }

            return $value;
        };

        return hash('sha256', json_encode($canonicalize(Arr::except($data, [])), JSON_THROW_ON_ERROR));
    }

    private function moneyToMinor(string|int|float $amount): int
    {
        return (int) round((float) $amount * 100);
    }

    private function minorToMoney(int $amount): string
    {
        return number_format($amount / 100, 2, '.', '');
    }

    private function generateOrderNumber(): string
    {
        do {
            $number = 'GR-OFF-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (Order::query()->where('order_number', $number)->exists());

        return $number;
    }
}
