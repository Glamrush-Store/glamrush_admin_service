<?php

namespace App\Http\Controllers\Collection;

use App\Domain\Collection\UseCases\CreateCollectionUseCase;
use App\Http\Requests\Collection\CollectionRequest;
use App\Http\Resources\Collection\CollectionResource;
use App\Http\Responses\ApiResponse;

class CreateCollectionController
{
    public function __construct(private CreateCollectionUseCase $useCase) {}

    public function __invoke(CollectionRequest $request)
    {
        $collection = $this->useCase->run(
            data: $request->validated(),
            photo: $request->file('photo')
        );

        return ApiResponse::success(new CollectionResource($collection), 'OK', 201);
    }
}
