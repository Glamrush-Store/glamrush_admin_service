<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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

    protected static function booted()
    {
        static::saving(function ($model) {
            if (is_array($model->attributes['attributes'])) {
                $model->attributes = collect($model->attributes['attributes'])
                    ->mapWithKeys(fn ($item) => [
                        $item['key'] => $item['value'],
                    ])
                    ->toArray();
            }
        });
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
