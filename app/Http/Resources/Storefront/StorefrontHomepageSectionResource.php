<?php

namespace App\Http\Resources\Storefront;

use App\Domain\Storefront\Enums\HomepageSectionType;
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

        return [
            'id' => $this->id,
            'storefront_slug' => $this->storefront_slug,
            'type' => $this->type->value,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'config' => $config,
            'display_order' => $this->display_order,
            'is_active' => $this->is_active,
            'starts_at' => $this->starts_at?->toISOString(),
            'ends_at' => $this->ends_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
