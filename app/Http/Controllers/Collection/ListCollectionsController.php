<?php

namespace App\Http\Controllers\Collection;

use App\Domain\Collection\UseCases\ListCollectionsUseCase;
use App\Http\Resources\Collection\CollectionListResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\Request;

class ListCollectionsController
{
    public function __construct(private ListCollectionsUseCase $useCase) {}

    public function __invoke(Request $request)
    {
        $collections = $this->useCase->run(
            filters: $request->input(),
            perPage: $request->integer('per_page', 15)
        );

        return ApiResponse::success(CollectionListResource::collection($collections));
    }
}
