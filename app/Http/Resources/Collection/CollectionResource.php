<?php

namespace App\Http\Resources\Collection;

use App\Http\Resources\Product\ProductListResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CollectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'sort_order' => (int) $this->sort_order,
            'is_active' => (bool) $this->is_active,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keywords,
            'image' => $this->getFirstMediaUrl('catalog-photos') == "" ? null : [
                'url' => $this->getFirstMediaUrl('catalog-photos') ?: null,
                'thumb' => $this->getFirstMediaUrl('catalog-photos', 'thumb') ?: null,
                'medium' => $this->getFirstMediaUrl('catalog-photos', 'medium') ?: null,
            ],
            'products' => ProductListResource::collection($this->whenLoaded('products')),
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}
