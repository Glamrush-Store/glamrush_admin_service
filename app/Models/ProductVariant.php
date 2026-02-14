<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ProductVariant extends Model implements HasMedia
{
    use HasFactory, HasUlids, InteractsWithMedia;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'product_id',
        'sku',
        'is_default',
        'price',
        'sale_price',
        'sale_starts_at',
        'sale_ends_at',
        'manage_stock',
        'stock_quantity',
        'in_stock',
        'attributes',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'attributes' => 'array',
    ];

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

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
