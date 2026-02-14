<?php

namespace App\Models;

use App\Infrastructure\Cache\CatalogCache;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements hasMedia
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
        'category_id',
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

        static::saving(function ($product) {
            if ($product->type === 'simple') {
                $product->variants()->delete();
            }
        });
    }

    /**
     * All variants belonging to this product.
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)
            ->orderBy('sort_order');
    }

    /*
     |--------------------------------------------------------------------------
     | Relationships
     |--------------------------------------------------------------------------
     */

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('catalog-photos')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }


    public function registerMediaConversions(
        \Spatie\MediaLibrary\MediaCollections\Models\Media $media = null
    ): void {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 400, 400)
            ->sharpen(10)
            ->nonQueued();

        $this->addMediaConversion('medium')
            ->fit(Fit::Max, 800, 800)
            ->nonQueued();

        $this->addMediaConversion('large')
            ->fit(Fit::Max, 1600, 1600)
            ->nonQueued();
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Default variant (used for simple products).
     */
    public function defaultVariant(): HasOne
    {
        return $this->hasOne(ProductVariant::class)
            ->where('is_default', true);
    }

    /*
     |--------------------------------------------------------------------------
     | Convenience helpers
     |--------------------------------------------------------------------------
     */

    /**
     * Determine if the product is variable.
     */
    public function isVariable(): bool
    {
        return $this->type === 'variable';
    }

    /**
     * Determine if the product is simple.
     */
    public function isSimple(): bool
    {
        return $this->type === 'simple';
    }

    /**
     * Get the variant that should be sold by default.
     */
    public function sellableVariant(): ?ProductVariant
    {
        return $this->defaultVariant
            ?? $this->variants()->first();
    }
}
