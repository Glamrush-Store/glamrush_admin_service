<?php

namespace App\Domain\Shipping\LocationOptions\UseCases;

use App\Domain\Shipping\LocationOptions\Actions\ReadCountryLocationOptionsAction;

class ListCountriesUseCase
{
    public function __construct(private ReadCountryLocationOptionsAction $locationOptions) {}

    public function run(): array
    {
        return $this->locationOptions->countries();
    }
}
