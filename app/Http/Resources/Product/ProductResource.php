<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'images_debug' => $this->media,
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

            'images' => $this->getMedia('catalog-photos')->map(function ($media) {
                return [
                    'id' => $media->id,
                    'name' => $media->name,
                    'url' => $media->getUrl(),
                    'thumb' => $media->getUrl('thumb'),
                    'medium' => $media->getUrl('medium'),
                ];
            }),

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

            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                    'slug' => $this->category->slug,
                ];
            }),

            'brand' => $this->whenLoaded('brand', function () {
                return [
                    'id' => $this->brand->id,
                    'name' => $this->brand->name,
                    'code' => $this->brand->code,
                    'slug' => $this->brand->slug,
                ];
            }),

            'vendor' => $this->whenLoaded('vendor', function () {
                return [
                    'id' => $this->vendor->id,
                    'name' => $this->vendor->name,
                    'business_name' => $this->vendor->business_name,
                    'code' => $this->vendor->code,
                ];
            }),

            'variants' => $this->whenLoaded('variants', function () {
                return $this->variants->map(function ($variant) {
                    return [
                        'id' => $variant->id,
                        'sku' => $variant->sku,
                        'is_default' => $variant->is_default,

                        'images' => $variant->getMedia('variant-photos')->map(function ($media) {
                            return [
                                'id' => $media->id,
                                'name' => $media->name,
                                'url' => $media->getUrl(),
                                'thumb' => $media->getUrl('thumb'),
                                'medium' => $media->getUrl('medium'),
                            ];
                        }),


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
                    ];
                });
            }),

            'timestamps' => [
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
        ];
    }
}
