<?php

namespace App\Domain\Collection\UseCases;

use App\Models\Collection;

class DeleteCollectionUseCase
{
    public function run(Collection $collection): void
    {
        $collection->delete();
    }
}
