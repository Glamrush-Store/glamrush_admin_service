<?php

namespace App\Http\Resources\Collection;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CollectionListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sort_order' => (int) $this->sort_order,
            'is_active' => (bool) $this->is_active,
            'image' => $this->getFirstMediaUrl('catalog-photos') == "" ? null : [
                'url' => $this->getFirstMediaUrl('catalog-photos') ?: null,
                'thumb' => $this->getFirstMediaUrl('catalog-photos', 'thumb') ?: null,
                'medium' => $this->getFirstMediaUrl('catalog-photos', 'medium') ?: null,
            ],
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}
