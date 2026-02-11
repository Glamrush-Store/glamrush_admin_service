<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Controllers\Brand;

use App\Domain\Brand\UseCases\DeleteBrandUseCase;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;

class DeleteBrandController extends Controller
{
    public function __construct(private DeleteBrandUseCase $useCase) {}

    public function __invoke(
        Brand $brand,
    ): JsonResponse {

        $this->useCase->run($brand);

        return ApiResponse::success([], '', 204);

    }
}
