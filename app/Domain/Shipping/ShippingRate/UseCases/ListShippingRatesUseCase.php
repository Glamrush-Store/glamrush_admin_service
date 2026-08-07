<?php

namespace App\Domain\Shipping\ShippingRate\UseCases;

use App\Domain\Shipping\ShippingRate\Actions\BuildShippingRateQueryAction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListShippingRatesUseCase
{
    public function __construct(private BuildShippingRateQueryAction $buildQuery) {}

    public function run(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->buildQuery->run($filters)->paginate($perPage);
    }
}
