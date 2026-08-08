<?php

namespace App\Http\Resources\Discount;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiscountCodeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id, 'code' => $this->code, 'name' => $this->name, 'description' => $this->description,
            'type' => $this->type->value, 'value' => $this->value, 'currency' => $this->currency,
            'maximum_discount_amount' => $this->maximum_discount_amount, 'minimum_subtotal' => $this->minimum_subtotal,
            'starts_at' => $this->starts_at?->toISOString(), 'ends_at' => $this->ends_at?->toISOString(),
            'is_active' => $this->is_active, 'state' => $this->state(), 'total_usage_limit' => $this->total_usage_limit,
            'per_customer_usage_limit' => $this->per_customer_usage_limit, 'first_order_only' => $this->first_order_only,
            'applies_to_sale_items' => $this->applies_to_sale_items, 'applies_to_all_storefronts' => $this->applies_to_all_storefronts,
            'storefronts' => $this->whenLoaded('storefronts', fn () => $this->storefronts->map->only(['id', 'name', 'slug'])),
            'targets' => $this->whenLoaded('targets', fn () => $this->targets->map(fn ($target) => ['id' => $target->id, 'target_type' => $target->target_type->value, 'target_id' => $target->target_id, 'mode' => $target->mode->value])),
            'created_by' => $this->whenLoaded('createdBy', fn () => $this->createdBy ? $this->createdBy->only(['id', 'name']) : null),
            'updated_by' => $this->whenLoaded('updatedBy', fn () => $this->updatedBy ? $this->updatedBy->only(['id', 'name']) : null),
            'created_at' => $this->created_at?->toISOString(), 'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
