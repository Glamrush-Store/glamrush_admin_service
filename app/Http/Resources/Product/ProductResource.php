<?php

namespace App\Http\Resources\Product;

use App\Support\Media\SafeMediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $primaryCategory = $this->primaryCategoryValue();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'sku' => $this->sku,
            'sequence' => $this->sequence,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'type' => $this->type,
            'status' => $this->status,
            'published_at' => $this->published_at,
            'meta' => [
                'title' => $this->meta_title,
                'keywords' => $this->meta_keywords,
                'description' => $this->meta_description,
            ],
            'images' => $this->getMedia('catalog-photos')->map(
                fn ($media) => SafeMediaUrl::image($media)
            ),
            'pricing' => [
                'price' => $this->price,
                'sale_price' => $this->sale_price,
                'sale_starts_at' => $this->sale_starts_at,
                'sale_ends_at' => $this->sale_ends_at,
            ],
            'inventory' => [
                'manage_stock' => $this->manage_stock,
                'stock_quantity' => $this->stock_quantity,
                'in_stock' => $this->in_stock,
            ],
            'flags' => [
                'is_featured' => $this->is_featured,
                'sort_order' => $this->sort_order,
            ],
            'metrics' => [
                'views_count' => $this->views_count,
                'sales_count' => $this->sales_count,
            ],
            'category' => $primaryCategory,
            'primary_category' => $primaryCategory,
            'categories' => $this->whenLoaded('categories', fn () => $this->categories->map(fn ($category) => $this->categoryData($category))),
            'brand' => $this->whenLoaded('brand', fn () => [
                'id' => $this->brand->id,
                'name' => $this->brand->name,
                'code' => $this->brand->code,
                'slug' => $this->brand->slug,
            ]),
            'vendor' => $this->whenLoaded('vendor', fn () => [
                'id' => $this->vendor->id,
                'name' => $this->vendor->name,
                'business_name' => $this->vendor->business_name,
                'code' => $this->vendor->code,
            ]),
            'collections' => $this->whenLoaded('collections', fn () => $this->collections->map(fn ($collection) => [
                'id' => $collection->id,
                'name' => $collection->name,
                'slug' => $collection->slug,
            ])),
            'variants' => $this->whenLoaded('variants', fn () => $this->variants->map(fn ($variant) => [
                'id' => $variant->id,
                'sku' => $variant->sku,
                'is_default' => $variant->is_default,
                'images' => $variant->getMedia('catalog-photos')->map(
                    fn ($media) => SafeMediaUrl::image($media)
                ),
                'pricing' => [
                    'price' => $variant->price,
                    'sale_price' => $variant->sale_price,
                    'sale_starts_at' => $variant->sale_starts_at,
                    'sale_ends_at' => $variant->sale_ends_at,
                ],
                'inventory' => [
                    'manage_stock' => $variant->manage_stock,
                    'stock_quantity' => $variant->stock_quantity,
                    'in_stock' => $variant->in_stock,
                ],
                'attributes' => $variant->attributes ?? [],
                'sort_order' => $variant->sort_order,
                'status' => $variant->status,
            ])),
            'timestamps' => [
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
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
