<?php

namespace App\Http\Resources\Content;

use App\Support\Media\SafeMediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContentPageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'navigation_title' => $this->navigation_title,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            'page_type' => $this->page_type->value,
            'settings' => $this->settings,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'is_published' => $this->is_published,
            'state' => $this->state(),
            'published_at' => $this->published_at?->toISOString(),
            'expires_at' => $this->expires_at?->toISOString(),
            'applies_to_all_storefronts' => $this->applies_to_all_storefronts,
            'storefronts' => $this->whenLoaded('storefronts', fn () => $this->storefronts->map->only(['id', 'name', 'slug'])),
            'display_order' => $this->display_order,
            'media' => $this->whenLoaded('media', fn () => $this->media->map(fn ($media) => [
                'id' => $media->id,
                'name' => $media->name,
                'url' => SafeMediaUrl::get($media),
            ])),
            'created_by' => $this->whenLoaded('createdBy', fn () => $this->createdBy?->only(['id', 'name'])),
            'updated_by' => $this->whenLoaded('updatedBy', fn () => $this->updatedBy?->only(['id', 'name'])),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
