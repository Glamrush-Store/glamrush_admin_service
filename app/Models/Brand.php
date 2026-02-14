<?php

namespace App\Models;

use App\Infrastructure\Cache\CatalogCache;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Brand extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\BrandFactory> */
    use HasFactory, HasUlids, InteractsWithMedia;

    public $incrementing = false;

    protected $keyType = 'string';

    protected static function booted(): void
    {
        static::saved(fn () => CatalogCache::flushBrands());
        static::deleted(fn () => CatalogCache::flushBrands());
    }

    protected $fillable = [
        'name',
        'slug',
        'code',
        'is_active',
        'description',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'sort_order',
        'logo',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('brand_images')
            ->useDisk('gcs')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'])
            ->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->sharpen(10);

        $this->addMediaConversion('medium')
            ->fit(Fit::Max, 800, 800)
            ->nonQueued();
    }
}
