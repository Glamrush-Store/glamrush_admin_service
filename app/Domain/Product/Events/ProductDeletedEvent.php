<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Product\Events;

use App\Models\Product;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

class ProductDeletedEvent implements ShouldDispatchAfterCommit
{
    public function __construct(
        public readonly Product $Product
    ) {}
}
