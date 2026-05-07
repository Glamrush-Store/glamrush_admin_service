<?php

namespace App\Domain\Shipping\Shipment\UseCases;

use App\Domain\Shipping\Shipment\Actions\BuildShipmentQueryAction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListShipmentsUseCase
{
    public function __construct(private BuildShipmentQueryAction $buildQuery) {}

    public function run(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->buildQuery->run($filters)->paginate($perPage);
    }
}
