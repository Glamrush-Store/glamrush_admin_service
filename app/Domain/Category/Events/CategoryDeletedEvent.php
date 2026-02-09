<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Category\Events;

use App\Models\Category;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

class CategoryDeletedEvent implements ShouldDispatchAfterCommit
{
    public function __construct(
        public readonly Category $category
    ) {}
}
