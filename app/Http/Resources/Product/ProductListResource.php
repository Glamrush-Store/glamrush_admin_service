<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $primaryCategory = $this->primaryCategoryValue();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->type,
            'status' => $this->status,
            'price' => (float) $this->price,
            'sale_price' => $this->sale_price ? (float) $this->sale_price : null,
            'category' => $primaryCategory,
            'primary_category' => $primaryCategory,
            'categories' => $this->whenLoaded('categories', fn () => $this->categories->map(fn ($category) => $this->categoryData($category))),
            'brand' => $this->whenLoaded('brand', fn () => [
                'id' => $this->brand->id,
                'name' => $this->brand->name,
                'slug' => $this->brand->slug,
            ]),
            'vendor' => $this->whenLoaded('vendor', fn () => [
                'id' => $this->vendor->id,
                'business_name' => $this->vendor->business_name,
            ]),
            'images' => $this->getMedia('catalog-photos')->map(fn ($media) => [
                'id' => $media->id,
                'name' => $media->name,
                'url' => $media->getUrl(),
                'thumb' => $media->getUrl('thumb'),
                'medium' => $media->getUrl('medium'),
            ]),
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }

    private function primaryCategoryValue(): ?array
    {
        if ($this->relationLoaded('primaryCategory') && $this->primaryCategory->isNotEmpty()) {
            return $this->categoryData($this->primaryCategory->first());
        }

        if ($this->relationLoaded('categories')) {
            $category = $this->categories->first(fn ($category) => (bool) ($category->pivot?->is_primary));
            return $category ? $this->categoryData($category) : null;
        }

        return null;
    }

    private function categoryData($category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'is_primary' => (bool) ($category->pivot?->is_primary ?? false),
            'sequence' => isset($category->pivot) ? (int) $category->pivot->sequence : null,
        ];
    }
}
