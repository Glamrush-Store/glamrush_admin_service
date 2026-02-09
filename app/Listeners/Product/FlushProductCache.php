<?php
/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Listeners\Product;

use App\Domain\Product\Events\ProductDeletedEvent;
use App\Domain\Product\Events\ProductSavedEvent;
use App\Infrastructure\Cache\CatalogCache;

class FlushProductCache
{
    public function handle(ProductSavedEvent|ProductDeletedEvent $event): void
    {
        CatalogCache::flushProducts();
    }
}
