<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Infrastructure\Cache;

use Illuminate\Support\Facades\Cache;

final class CatalogCache
{
    public static function flushProducts(): void
    {
        Cache::tags(['catalog', 'products'])->flush();
    }

    public static function flushCategories(): void
    {
        Cache::tags(['catalog', 'categories'])->flush();
    }

    public static function flushBrands(): void
    {
        Cache::tags(['catalog', 'brands'])->flush();
    }

    public static function flushAll(): void
    {
        Cache::tags(['catalog'])->flush();
    }
}
