<?php

namespace App\Http\Controllers\Shipping\LocationOptions;

use App\Domain\Shipping\LocationOptions\UseCases\ListCountryStatesAndCitiesUseCase;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;

class ListCountryStatesAndCitiesController extends Controller
{
    public function __construct(private ListCountryStatesAndCitiesUseCase $useCase) {}

    public function __invoke(string $country)
    {
        return ApiResponse::success($this->useCase->run($country));
    }
}
