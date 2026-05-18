<?php

namespace App\Domain\Order\UseCases;

use App\Domain\Order\Actions\BuildOrderQueryAction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListOrdersUseCase
{
    public function __construct(private BuildOrderQueryAction $buildQuery) {}

    public function run(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->buildQuery->run($filters)->paginate($perPage);
    }
}
