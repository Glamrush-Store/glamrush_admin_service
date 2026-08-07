<?php

namespace App\Http\Resources\Shipping;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShipmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'shipping_amount' => $this->shipping_amount,
            'status' => $this->status,
            'tracking_number' => $this->tracking_number,
            'carrier' => $this->carrier,
            'shipped_at' => optional($this->shipped_at)->toISOString(),
            'delivered_at' => optional($this->delivered_at)->toISOString(),
            'method' => $this->whenLoaded('method', fn () => $this->method?->only(['id', 'name', 'code'])),
            'zone' => $this->whenLoaded('zone', fn () => $this->zone?->only(['id', 'name'])),
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}
