<?php

namespace App\Http\Resources\Storefront;

use App\Support\Media\SafeMediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StorefrontCampaignResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $desktopImage = $this->getFirstMedia('desktop-image');
        $mobileImage = $this->getFirstMedia('mobile-image');

        return [
            'id' => $this->id,
            'storefront_slug' => $this->storefront_slug,
            'internal_name' => $this->internal_name,
            'eyebrow' => $this->eyebrow,
            'title' => $this->title,
            'description' => $this->description,
            'desktop_image' => $desktopImage ? SafeMediaUrl::get($desktopImage) : '',
            'mobile_image' => $mobileImage ? SafeMediaUrl::get($mobileImage) : '',
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
