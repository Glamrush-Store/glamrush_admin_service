<?php

namespace App\Http\Resources\Shipping;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShippingRateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rate_type' => $this->rate_type,
            'amount' => $this->amount,
            'free_over_amount' => $this->free_over_amount,
            'min_order_amount' => $this->min_order_amount,
            'max_order_amount' => $this->max_order_amount,
            'estimated_days_min' => $this->estimated_days_min,
            'estimated_days_max' => $this->estimated_days_max,
            'is_active' => $this->is_active,
            'zone' => $this->whenLoaded('zone', fn () => $this->zone?->only(['id', 'name'])),
            'method' => $this->whenLoaded('method', fn () => $this->method?->only(['id', 'name', 'code'])),
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}
