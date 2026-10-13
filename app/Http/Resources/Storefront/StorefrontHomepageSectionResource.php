<?php

namespace App\Http\Resources\Storefront;

use App\Domain\Storefront\Enums\HomepageSectionType;
use App\Support\Media\SafeMediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StorefrontHomepageSectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $config = $this->config;
        if ($this->type === HomepageSectionType::ManualProducts) {
            $config['product_ids'] = $this->products->pluck('id')->values()->all();
        }
        if ($this->type === HomepageSectionType::SplitImageBanner) {
            $config['items'] = collect($config['items'] ?? [])->sortBy('sort_order')->values()->all();
        }

        return [
            'id' => $this->id,
            'storefront_slug' => $this->storefront_slug,
            'type' => $this->type->value,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'config' => $config,
            'images' => $this->type === HomepageSectionType::SplitImageBanner ? [
                'left' => $this->mediaUrl('left-image'),
                'right' => $this->mediaUrl('right-image'),
                'mobile' => $this->mediaUrl('mobile-image'),
            ] : null,
            'display_order' => $this->display_order,
            'is_active' => $this->is_active,
            'starts_at' => $this->starts_at?->toISOString(),
            'ends_at' => $this->ends_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    private function mediaUrl(string $collection): string
    {
        $media = $this->getFirstMedia($collection);

        return $media ? SafeMediaUrl::get($media) : '';
    }
}
