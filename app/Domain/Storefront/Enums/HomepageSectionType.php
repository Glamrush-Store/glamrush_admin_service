<?php

namespace App\Domain\Storefront\Enums;

enum HomepageSectionType: string
{
    case FeaturedProducts = 'featured_products';
    case SaleProducts = 'sale_products';
    case CategoryProducts = 'category_products';
    case CollectionProducts = 'collection_products';
    case NewestProducts = 'newest_products';
    case RandomCategories = 'random_categories';
    case ManualProducts = 'manual_products';

    /** @return list<string> */
    public function allowedConfigKeys(): array
    {
        return match ($this) {
            self::FeaturedProducts => ['limit', 'sort', 'direction'],
            self::SaleProducts, self::NewestProducts => ['limit'],
            self::CategoryProducts => ['category_slug', 'limit', 'sort', 'direction'],
            self::CollectionProducts => ['collection_slug', 'limit'],
            self::RandomCategories => ['limit', 'require_products'],
            self::ManualProducts => ['product_ids', 'limit'],
        };
    }
}
