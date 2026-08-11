<?php

namespace App\Http\Controllers\Shipping\LocationOptions;

use App\Domain\Shipping\LocationOptions\UseCases\ListCountriesUseCase;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;

class ListCountriesController extends Controller
{
    public function __construct(private ListCountriesUseCase $useCase) {}

    public function __invoke()
    {
        return ApiResponse::success($this->useCase->run());
    }
}
