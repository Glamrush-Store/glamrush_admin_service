<?php

namespace App\Http\Resources\Order;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status,
            'customer' => $this->customerData(),
            'guest_id' => $this->guest_id,
            'shipping_status' => $this->whenLoaded('shipment', fn () => $this->shipment?->status),
            'payment_status' => $this->whenLoaded('latestPayment', fn () => $this->latestPayment?->status),
            'payment_provider' => $this->whenLoaded('latestPayment', fn () => $this->latestPayment?->provider),
            'subtotal' => $this->subtotal,
            'shipping_amount' => $this->shipping_amount,
            'total' => $this->total,
            'currency' => $this->currency,
            'placed_at' => optional($this->placed_at)->toISOString(),
            'paid_at' => optional($this->paid_at)->toISOString(),
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }

    private function customerData(): array
    {
        if ($this->relationLoaded('customer') && $this->customer) {
            [$firstName, $lastName] = $this->splitName($this->customer->name);

            return [
                'type' => 'customer',
                'id' => $this->customer->id,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'name' => $this->customer->name,
                'email' => $this->customer->email,
                'phone' => $this->customer->phone,
            ];
        }

        $shippingAddress = is_array($this->shipping_address) ? $this->shipping_address : [];
        $fullName = (string) ($shippingAddress['full_name'] ?? $shippingAddress['name'] ?? '');
        [$firstName, $lastName] = $this->splitName($fullName);

        return [
            'type' => 'guest',
            'id' => null,
            'guest_id' => $this->guest_id,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'name' => $fullName ?: null,
            'email' => $shippingAddress['email'] ?? null,
            'phone' => $shippingAddress['phone'] ?? null,
        ];
    }

    /**
     * @return array{0: string|null, 1: string|null}
     */
    private function splitName(?string $fullName): array
    {
        $fullName = trim((string) $fullName);

        if ($fullName === '') {
            return [null, null];
        }

        $parts = preg_split('/\s+/', $fullName, 2) ?: [];

        return [
            $parts[0] ?? null,
            $parts[1] ?? null,
        ];
    }
}
