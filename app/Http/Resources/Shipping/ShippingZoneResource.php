<?php

namespace App\Http\Resources\Shipping;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShippingZoneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'country' => $this->country,
            'state' => $this->state,
            'city' => $this->city,
            'postal_code_pattern' => $this->postal_code_pattern,
            'is_active' => $this->is_active,
            'rates' => $this->whenLoaded('rates', fn () => $this->rates->map(fn ($rate) => [
                'id' => $rate->id,
                'rate_type' => $rate->rate_type,
                'amount' => $rate->amount,
                'method' => $rate->method?->only(['id', 'name', 'code']),
            ])),
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}
