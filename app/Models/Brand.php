<?php

namespace App\Models;

use App\Infrastructure\Cache\CatalogCache;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Brand extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\BrandFactory> */
    use HasFactory, HasUlids, InteractsWithMedia;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'slug',
        'is_active',
        'description',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'sort_order',
        'logo',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => CatalogCache::flushBrands());
        static::deleted(fn () => CatalogCache::flushBrands());
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }
}
