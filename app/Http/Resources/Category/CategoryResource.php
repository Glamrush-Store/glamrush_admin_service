<?php

namespace App\Http\Resources\Category;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
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
            'slug' => $this->slug,
            'parent_id' => $this->parent_id,

            'sort_order' => (int) $this->sort_order,
            'is_active' => (bool) $this->is_active,
            'image' => [
                'url' => $this->getFirstMediaUrl('catalog-photos'),
                'thumb' => $this->getFirstMediaUrl('catalog-photos', 'thumb'),
                'medium' => $this->getFirstMediaUrl('catalog-photos', 'medium'),
            ],

            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}
