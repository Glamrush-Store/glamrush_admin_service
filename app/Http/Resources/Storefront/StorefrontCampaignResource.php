<?php

namespace App\Http\Resources\Storefront;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StorefrontCampaignResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'storefront_slug' => $this->storefront_slug,
            'internal_name' => $this->internal_name,
            'eyebrow' => $this->eyebrow,
            'title' => $this->title,
            'description' => $this->description,
            'desktop_image' => $this->getFirstMediaUrl('desktop-image') ?: null,
            'mobile_image' => $this->getFirstMediaUrl('mobile-image') ?: null,
            'cta_label' => $this->cta_label,
            'cta_url' => $this->cta_url,
            'priority' => $this->priority,
            'is_active' => $this->is_active,
            'starts_at' => $this->starts_at?->toISOString(),
            'ends_at' => $this->ends_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
