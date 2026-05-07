<?php

namespace App\Domain\Collection\UseCases;

use App\Models\Collection;

class ShowCollectionUseCase
{
    public function run(Collection $collection): Collection
    {
        return $collection->load('products');
    }
}
