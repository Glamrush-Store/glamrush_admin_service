<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Listeners\Category;

use App\Domain\Category\Events\CategoryDeletedEvent;
use App\Domain\Category\Events\CategorySavedEvent;
use App\Infrastructure\Cache\CatalogCache;

class FlushCategoryCache
{
    public function handle(CategorySavedEvent|CategoryDeletedEvent $event): void
    {
        CatalogCache::flushCategories();
    }
}
