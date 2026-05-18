<?php

namespace App\Http\Resources\Order;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status,
            'customer_info' => $this->customerInfo(),
            'shipping_info' => $this->shippingInfo(),
            'payment_info' => $this->paymentInfo(),
            'order_info' => [
                'subtotal' => $this->subtotal,
                'shipping_amount' => $this->shipping_amount,
                'total' => $this->total,
                'currency' => $this->currency,
                'placed_at' => optional($this->placed_at)->toISOString(),
                'paid_at' => optional($this->paid_at)->toISOString(),
                'expires_at' => optional($this->expires_at)->toISOString(),
                'cancelled_at' => optional($this->cancelled_at)->toISOString(),
                'created_at' => optional($this->created_at)->toISOString(),
                'updated_at' => optional($this->updated_at)->toISOString(),
            ],
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
        ];
    }

    private function customerInfo(): array
    {
        if ($this->customer) {
            return [
                'type' => 'customer',
                'id' => $this->customer->id,
                'name' => $this->customer->name,
                'email' => $this->customer->email,
                'phone' => $this->customer->phone,
                'email_verified_at' => optional($this->customer->email_verified_at)->toISOString(),
                'created_at' => optional($this->customer->created_at)->toISOString(),
            ];
        }

        return [
            'type' => 'guest',
            'guest_id' => $this->guest_id,
        ];
    }

    private function shippingInfo(): array
    {
        return [
            'shipping_rate_id' => $this->shipping_rate_id,
            'shipping_method_name' => $this->shipping_method_name,
            'shipping_zone_name' => $this->shipping_zone_name,
            'shipping_address' => $this->shipping_address,
            'billing_address' => $this->billing_address,
            'shipment' => $this->whenLoaded('shipment', fn () => $this->shipment ? [
                'id' => $this->shipment->id,
                'status' => $this->shipment->status,
                'shipping_amount' => $this->shipment->shipping_amount,
                'tracking_number' => $this->shipment->tracking_number,
                'carrier' => $this->shipment->carrier,
                'shipped_at' => optional($this->shipment->shipped_at)->toISOString(),
                'delivered_at' => optional($this->shipment->delivered_at)->toISOString(),
                'method' => $this->shipment->relationLoaded('method') && $this->shipment->method ? $this->shipment->method->only(['id', 'name', 'code']) : null,
                'zone' => $this->shipment->relationLoaded('zone') && $this->shipment->zone ? $this->shipment->zone->only(['id', 'name']) : null,
            ] : null),
            'shipping_rate' => $this->whenLoaded('shippingRate', fn () => $this->shippingRate ? [
                'id' => $this->shippingRate->id,
                'amount' => $this->shippingRate->amount,
                'rate_type' => $this->shippingRate->rate_type,
                'method' => $this->shippingRate->relationLoaded('method') && $this->shippingRate->method ? $this->shippingRate->method->only(['id', 'name', 'code']) : null,
                'zone' => $this->shippingRate->relationLoaded('zone') && $this->shippingRate->zone ? $this->shippingRate->zone->only(['id', 'name']) : null,
            ] : null),
        ];
    }

    private function paymentInfo(): ?array
    {
        if (! $this->latestPayment) {
            return null;
        }

        return [
            'id' => $this->latestPayment->id,
            'provider' => $this->latestPayment->provider,
            'reference' => $this->latestPayment->reference,
            'provider_reference' => $this->latestPayment->provider_reference,
            'transaction_id' => $this->latestPayment->transaction_id,
            'amount' => $this->latestPayment->amount,
            'currency' => $this->latestPayment->currency,
            'status' => $this->latestPayment->status,
            'authorization_url' => $this->latestPayment->authorization_url,
            'paid_at' => optional($this->latestPayment->paid_at)->toISOString(),
            'failed_at' => optional($this->latestPayment->failed_at)->toISOString(),
            'metadata' => $this->latestPayment->metadata,
            'payment_method' => $this->latestPayment->relationLoaded('paymentMethod') && $this->latestPayment->paymentMethod ? $this->latestPayment->paymentMethod->only(['id', 'name', 'code', 'description']) : null,
            'transactions' => $this->latestPayment->relationLoaded('transactions') ? $this->latestPayment->transactions->map(fn ($transaction) => [
                'id' => $transaction->id,
                'type' => $transaction->type,
                'status' => $transaction->status,
                'provider_reference' => $transaction->provider_reference,
                'amount' => $transaction->amount,
                'currency' => $transaction->currency,
                'payload' => $transaction->payload,
                'created_at' => optional($transaction->created_at)->toISOString(),
            ])->values() : [],
        ];
    }
}

