<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Controllers\Brand;

use App\Domain\Brand\UseCases\UpdateBrandUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Brand\UpdateBrandRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;

class UpdateBrandController extends Controller
{
    public function __construct(private UpdateBrandUseCase $useCase) {}

    public function __invoke(
        Brand $brand,
        UpdateBrandRequest $request,
    ): JsonResponse {

        $brand = $this->useCase->run(
            brand: $brand,
            data: $request->validated(),
            photo: $request->file('image')
        );

        return ApiResponse::success($brand, 'OK', 200);
    }
}
