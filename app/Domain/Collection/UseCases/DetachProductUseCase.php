<?php

namespace App\Domain\Collection\UseCases;

use App\Models\Collection;
use App\Models\Product;

class DetachProductUseCase
{
    public function run(Collection $collection, Product $product): void
    {
        $collection->products()->detach($product->id);
    }
}
