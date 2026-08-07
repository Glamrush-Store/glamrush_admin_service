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
            'customer' => $this->whenLoaded('customer', fn () => $this->customer ? [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
                'email' => $this->customer->email,
                'phone' => $this->customer->phone,
            ] : null),
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
}

