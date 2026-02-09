<?php

namespace App\Models;

use App\Infrastructure\Cache\CatalogCache;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Category extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory, HasUlids, InteractsWithMedia;

    public $incrementing = false;

    protected $keyType = 'string';


    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'is_active',
        'description',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'sort_order',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => CatalogCache::flushCategories());
        static::deleted(fn () => CatalogCache::flushCategories());
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('category_images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'])
            ->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->sharpen(10);
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }
}
