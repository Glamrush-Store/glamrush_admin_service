<?php

namespace App\Models;

use App\Domain\Storefront\Enums\HomepageSectionType;
use App\Infrastructure\Cache\StorefrontHomepageCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class StorefrontHomepageSection extends Model implements HasMedia
{
    use HasUlids, InteractsWithMedia;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['storefront_slug', 'type', 'title', 'subtitle', 'config', 'display_order', 'is_active', 'starts_at', 'ends_at', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return ['type' => HomepageSectionType::class, 'config' => 'array', 'display_order' => 'integer', 'is_active' => 'boolean', 'starts_at' => 'datetime', 'ends_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::saved(fn (self $section) => StorefrontHomepageCache::forget($section->storefront_slug));
        static::deleted(fn (self $section) => StorefrontHomepageCache::forget($section->storefront_slug));
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'storefront_homepage_section_product', 'section_id', 'product_id')
            ->withPivot('display_order')->orderByPivot('display_order');
    }

    public function registerMediaCollections(): void
    {
        $mimeTypes = ['image/jpeg', 'image/png', 'image/webp'];

        $this->addMediaCollection('left-image')->acceptsMimeTypes($mimeTypes)->singleFile();
        $this->addMediaCollection('right-image')->acceptsMimeTypes($mimeTypes)->singleFile();
        $this->addMediaCollection('mobile-image')->acceptsMimeTypes($mimeTypes)->singleFile();
    }

    public function scopeCurrent(Builder $query, mixed $at = null): Builder
    {
        $at ??= now();

        return $query->where('is_active', true)
            ->where(fn (Builder $query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', $at))
            ->where(fn (Builder $query) => $query->whereNull('ends_at')->orWhere('ends_at', '>', $at));
    }
}
