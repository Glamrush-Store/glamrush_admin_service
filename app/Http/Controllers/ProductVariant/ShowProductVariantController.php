<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Controllers\ProductVariant;

use App\Domain\Product\ProductVariant\UseCases\ShowProductVariantUseCase;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductVariant\ProductVariantResource;
use App\Http\Responses\ApiResponse;
use App\Models\ProductVariant;

class ShowProductVariantController extends Controller
{
    public function __construct(private ShowProductVariantUseCase $useCase) {}

    public function __invoke(
         ProductVariant $variant,
    ) {

        $variant = $this->useCase->run($variant);


        return ApiResponse::success(new ProductVariantResource($variant));
    }
}
