<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Controllers\Brand;

use App\Domain\Brand\UseCases\ListBrandsUseCase;
use App\Http\Resources\Brand\BrandResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\Request;

class ListBrandsController
{
    public function __construct(private ListBrandsUseCase $useCase) {}

    public function __invoke(
        Request $request,
    ) {

        $result = $this->useCase->run(
            filters: $request->all(),
            perPage: $request->integer('per_page', 5)
        );

        return ApiResponse::success(BrandResource::collection($result));

    }
}
