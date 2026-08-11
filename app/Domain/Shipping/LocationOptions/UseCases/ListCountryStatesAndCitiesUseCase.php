<?php

namespace App\Domain\Shipping\LocationOptions\UseCases;

use App\Domain\Shipping\LocationOptions\Actions\ReadCountryLocationOptionsAction;
use App\Exceptions\BusinessException;

class ListCountryStatesAndCitiesUseCase
{
    public function __construct(private ReadCountryLocationOptionsAction $locationOptions) {}

    public function run(string $countryCode): array
    {
        $locations = $this->locationOptions->countryLocations($countryCode);

        if ($locations === null) {
            throw BusinessException::notFound('Country', $countryCode);
        }

        return $locations;
    }
}
