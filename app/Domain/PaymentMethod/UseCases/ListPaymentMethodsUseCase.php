<?php

namespace App\Domain\PaymentMethod\UseCases;

use App\Domain\PaymentMethod\Actions\BuildPaymentMethodQueryAction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListPaymentMethodsUseCase
{
    public function __construct(private BuildPaymentMethodQueryAction $buildQuery) {}

    public function run(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->buildQuery->run($filters)->paginate($perPage);
    }
}
