<?php

namespace App\Domain\Collection\UseCases;

use App\Domain\Collection\Actions\BuildCollectionQueryAction;

class ListCollectionsUseCase
{
    public function __construct(
        private BuildCollectionQueryAction $buildQuery
    ) {}

    public function run(array $filters, int $perPage)
    {
        return $this->buildQuery
            ->run($filters)
            ->paginate($perPage);
    }
}
