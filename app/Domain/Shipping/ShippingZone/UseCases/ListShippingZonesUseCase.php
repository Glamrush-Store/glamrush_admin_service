<?php

namespace App\Domain\Shipping\ShippingZone\UseCases;

use App\Domain\Shipping\ShippingZone\Actions\BuildShippingZoneQueryAction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListShippingZonesUseCase
{
    public function __construct(private BuildShippingZoneQueryAction $buildQuery) {}

    public function run(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->buildQuery->run($filters)->paginate($perPage);
    }
}
