<?php

namespace App\Http\Controllers\Collection;

use App\Domain\Collection\UseCases\DeleteCollectionUseCase;
use App\Http\Responses\ApiResponse;
use App\Models\Collection;

class DeleteCollectionController
{
    public function __construct(private DeleteCollectionUseCase $useCase) {}

    public function __invoke(Collection $collection)
    {
        $this->useCase->run($collection);

        return ApiResponse::success(null, 'Collection Deleted');
    }
}
