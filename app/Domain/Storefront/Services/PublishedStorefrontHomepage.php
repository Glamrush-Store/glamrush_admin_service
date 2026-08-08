<?php

namespace App\Domain\Storefront\Services;

use App\Domain\Storefront\Enums\HomepageSectionType;
use App\Infrastructure\Cache\StorefrontHomepageCache;
use App\Models\StorefrontCampaign;
use App\Models\StorefrontHomepageSection;
use Illuminate\Support\Facades\Cache;

class PublishedStorefrontHomepage
{
    public function get(string $storefront): array
    {
        return Cache::remember(
            StorefrontHomepageCache::key($storefront),
            now()->addSeconds((int) config('services.storefront_internal.cache_ttl', 300)),
            fn () => $this->build($storefront)
        );
    }

    private function build(string $storefront): array
    {
        $campaign = StorefrontCampaign::query()
            ->where('storefront_slug', $storefront)
            ->current()
            ->orderByDesc('priority')
            ->orderByDesc('updated_at')
            ->first();

        $sections = StorefrontHomepageSection::query()
            ->where('storefront_slug', $storefront)
            ->current()
            ->with('products:id')
            ->orderByRaw("CASE WHEN type = ? THEN 0 ELSE 1 END", [HomepageSectionType::RandomCategories->value])
            ->orderBy('display_order')
            ->orderBy('id')
            ->get();

        return [
            'campaign' => $campaign ? [
                'id' => $campaign->id,
                'internal_name' => $campaign->internal_name,
                'eyebrow' => $campaign->eyebrow,
                'title' => $campaign->title,
                'description' => $campaign->description,
                'desktop_image' => $campaign->getFirstMediaUrl('desktop-image') ?: null,
                'mobile_image' => $campaign->getFirstMediaUrl('mobile-image') ?: null,
                'cta_label' => $campaign->cta_label,
                'cta_url' => $campaign->cta_url,
                'starts_at' => $campaign->starts_at?->toISOString(),
                'ends_at' => $campaign->ends_at?->toISOString(),
            ] : null,
            'sections' => $sections->map(function (StorefrontHomepageSection $section): array {
                $config = $section->config;
                if ($section->type === HomepageSectionType::ManualProducts) {
                    $config['product_ids'] = $section->products->pluck('id')->values()->all();
                }

                return [
                    'id' => $section->id,
                    'type' => $section->type->value,
                    'title' => $section->title,
                    'subtitle' => $section->subtitle,
                    'display_order' => $section->display_order,
                    'config' => $config,
                ];
            })->all(),
        ];
    }
}
