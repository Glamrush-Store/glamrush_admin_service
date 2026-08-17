<?php

namespace App\Models;

use App\Infrastructure\Cache\CatalogCache;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use HasFactory, HasUlids, InteractsWithMedia, SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'slug',
        'vendor_id',
        'short_description',
        'description',
        'sequence',
        'type',
        'status',
        'published_at',
        'meta_title',
        'meta_description',
        'is_featured',
        'sort_order',
        'brand_id',
        'sku',
        'price',
        'sale_price',
        'sale_starts_at',
        'sale_ends_at',
        'manage_stock',
        'stock_quantity',
        'in_stock',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::created(fn () => CatalogCache::flushProducts());
        static::updated(fn () => CatalogCache::flushProducts());
        static::deleted(fn () => CatalogCache::flushProducts());

    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)
            ->orderBy('sort_order');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('catalog-photos')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function registerMediaConversions(
        ?\Spatie\MediaLibrary\MediaCollections\Models\Media $media = null
    ): void {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 400, 400)
            ->sharpen(10);

        $this->addMediaConversion('medium')
            ->fit(Fit::Max, 800, 800);

        $this->addMediaConversion('large')
            ->fit(Fit::Max, 1600, 1600);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_product')
            ->using(CategoryProduct::class)
            ->withPivot(['id', 'is_primary', 'sequence'])
            ->withTimestamps()
            ->orderByPivot('sequence');
    }

    public function primaryCategory(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_product')
            ->using(CategoryProduct::class)
            ->withPivot(['id', 'is_primary', 'sequence'])
            ->wherePivot('is_primary', true)
            ->withTimestamps();
    }

    public function category(): BelongsToMany
    {
        return $this->primaryCategory();
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function collections()
    {
        return $this->belongsToMany(Collection::class, 'collection_product')
            ->withPivot('sort_order', 'created_at');
    }

    public function defaultVariant(): HasOne
    {
        return $this->hasOne(ProductVariant::class)
            ->where('is_default', true);
    }

    public function isVariable(): bool
    {
        return $this->type === 'variable';
    }

    public function isSimple(): bool
    {
        return $this->type === 'simple';
    }

    public function sellableVariant(): ?ProductVariant
    {
        return $this->defaultVariant
            ?? $this->variants()->first();
    }
}
