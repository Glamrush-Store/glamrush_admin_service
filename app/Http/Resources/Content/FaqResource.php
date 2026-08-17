<?php

namespace App\Http\Resources\Content;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FaqResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'category' => $this->whenLoaded('category', fn () => $this->category?->only(['id', 'name', 'slug'])), 'question' => $this->question, 'answer' => $this->answer, 'display_order' => $this->display_order, 'is_published' => $this->is_published, 'state' => $this->state(), 'published_at' => $this->published_at?->toISOString(), 'expires_at' => $this->expires_at?->toISOString(), 'applies_to_all_storefronts' => $this->applies_to_all_storefronts, 'storefronts' => $this->whenLoaded('storefronts', fn () => $this->storefronts->map->only(['id', 'name', 'slug'])), 'created_by' => $this->whenLoaded('createdBy', fn () => $this->createdBy?->only(['id', 'name'])), 'updated_by' => $this->whenLoaded('updatedBy', fn () => $this->updatedBy?->only(['id', 'name'])), 'created_at' => $this->created_at?->toISOString(), 'updated_at' => $this->updated_at?->toISOString()];
    }
}
