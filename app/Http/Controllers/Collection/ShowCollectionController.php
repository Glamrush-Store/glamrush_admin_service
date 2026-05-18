<?php

namespace App\Http\Controllers\Collection;

use App\Domain\Collection\UseCases\ShowCollectionUseCase;
use App\Http\Resources\Collection\CollectionResource;
use App\Http\Responses\ApiResponse;
use App\Models\Collection;

class ShowCollectionController
{
    public function __construct(private ShowCollectionUseCase $useCase) {}

    public function __invoke(Collection $collection)
    {
        $collection = $this->useCase->run($collection);

        return ApiResponse::success(new CollectionResource($collection));
    }
}
