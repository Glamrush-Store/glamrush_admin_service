<?php

namespace App\Domain\Shipping\ShippingMethod\UseCases;

use App\Domain\Shipping\ShippingMethod\Actions\BuildShippingMethodQueryAction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListShippingMethodsUseCase
{
    public function __construct(private BuildShippingMethodQueryAction $buildQuery) {}

    public function run(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->buildQuery->run($filters)->paginate($perPage);
    }
}
