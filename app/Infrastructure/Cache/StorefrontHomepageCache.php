<?php

namespace App\Infrastructure\Cache;

use Illuminate\Support\Facades\Cache;

final class StorefrontHomepageCache
{
    public static function key(string $storefront): string
    {
        return 'storefront_homepage:'.strtolower($storefront);
    }

    public static function forget(string $storefront): void
    {
        Cache::forget(self::key($storefront));
    }
}
