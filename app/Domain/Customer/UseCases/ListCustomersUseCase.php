<?php

namespace App\Domain\Customer\UseCases;

use App\Domain\Customer\Actions\BuildCustomerQueryAction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListCustomersUseCase
{
    public function __construct(
        private BuildCustomerQueryAction $buildQuery
    ) {}

    public function run(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->buildQuery
            ->run($filters)
            ->paginate($perPage);
    }
}
