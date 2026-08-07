<?php

namespace App\Models;

use App\Infrastructure\Cache\StorefrontHomepageCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class StorefrontCampaign extends Model implements HasMedia
{
    use HasUlids, InteractsWithMedia;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['storefront_slug', 'internal_name', 'eyebrow', 'title', 'description', 'cta_label', 'cta_url', 'priority', 'is_active', 'starts_at', 'ends_at', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'starts_at' => 'datetime', 'ends_at' => 'datetime', 'priority' => 'integer'];
    }

    protected static function booted(): void
    {
        static::saved(fn (self $campaign) => StorefrontHomepageCache::forget($campaign->storefront_slug));
        static::deleted(fn (self $campaign) => StorefrontHomepageCache::forget($campaign->storefront_slug));
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('desktop-image')->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])->singleFile();
        $this->addMediaCollection('mobile-image')->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])->singleFile();
    }

    public function scopeCurrent(Builder $query, mixed $at = null): Builder
    {
        $at ??= now();

        return $query->where('is_active', true)
            ->where(fn (Builder $query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', $at))
            ->where(fn (Builder $query) => $query->whereNull('ends_at')->orWhere('ends_at', '>', $at));
    }
}
