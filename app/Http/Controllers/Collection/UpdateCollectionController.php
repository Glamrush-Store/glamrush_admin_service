<?php

namespace App\Http\Controllers\Collection;

use App\Domain\Collection\UseCases\UpdateCollectionUseCase;
use App\Http\Requests\Collection\CollectionRequest;
use App\Http\Resources\Collection\CollectionResource;
use App\Http\Responses\ApiResponse;
use App\Models\Collection;

class UpdateCollectionController
{
    public function __construct(private UpdateCollectionUseCase $useCase) {}

    public function __invoke(Collection $collection, CollectionRequest $request)
    {
        $collection = $this->useCase->run(
            collection: $collection,
            data: $request->validated(),
            photo: $request->file('photo')
        );

        return ApiResponse::success(new CollectionResource($collection), 'Collection Updated');
    }
}
