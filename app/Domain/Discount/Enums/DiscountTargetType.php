<?php

namespace App\Domain\Discount\Enums;

enum DiscountTargetType: string
{
    case Product = 'product';
    case ProductVariant = 'product_variant';
    case Category = 'category';
    case Brand = 'brand';
    case Collection = 'collection';

    public function table(): string
    {
        return match ($this) {
            self::Product => 'products',
            self::ProductVariant => 'product_variants',
            self::Category => 'categories',
            self::Brand => 'brands',
            self::Collection => 'collections',
        };
    }
}
