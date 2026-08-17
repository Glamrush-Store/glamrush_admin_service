<?php

namespace App\Http\Resources\Category;

use App\Support\Media\SafeMediaUrl;
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
        $media = $this->getFirstMedia('catalog-photos');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'parent_id' => $this->parent_id,
            'announcement_primary_text' => $this->announcement_primary_text,
            'announcement_secondary_text' => $this->announcement_secondary_text,

            'sort_order' => (int) $this->sort_order,
            'is_active' => (bool) $this->is_active,
            'image' => $media ? SafeMediaUrl::image($media) : null,

            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}
