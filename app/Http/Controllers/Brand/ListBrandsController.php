<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Controllers\Brand;

use App\Domain\Brand\UseCases\ListBrandsUseCase;
use App\Http\Requests\Brand\ListBrandsRequest;
use App\Http\Resources\Brand\BrandResource;
use App\Http\Responses\ApiResponse;

class ListBrandsController
{
    public function __construct(private ListBrandsUseCase $useCase) {}

    public function __invoke(
        ListBrandsRequest $request,
    ) {
        $filters = $request->validated();

        $result = $this->useCase->run(
            filters: $filters,
            perPage: (int) ($filters['per_page'] ?? 15)
        );

        return ApiResponse::success(BrandResource::collection($result));

    }
}
