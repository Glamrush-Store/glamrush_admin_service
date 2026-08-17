<?php

namespace App\Http\Resources\ProductVariant;

use App\Support\Media\SafeMediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'sku' => $this->sku,
            'is_default' => (bool) $this->is_default,

            'price' => $this->price,
            'sale_price' => $this->sale_price,
            'sale_starts_at' => $this->sale_starts_at,
            'sale_ends_at' => $this->sale_ends_at,

            'manage_stock' => (bool) $this->manage_stock,
            'stock_quantity' => $this->stock_quantity,
            'in_stock' => (bool) $this->in_stock,

            'attributes' => empty($this->attributes)
                ? null
                : collect($this->attributes)->map(fn ($attribute) => [
                    'type' => $attribute['type'],
                    'value' => $attribute['value'],
                ]),

            'images' => $this->getMedia('catalog-photos')->map(
                fn ($media) => SafeMediaUrl::image($media)
            ),

            'sort_order' => $this->sort_order,
            'status' => $this->status,

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];

    }
}
