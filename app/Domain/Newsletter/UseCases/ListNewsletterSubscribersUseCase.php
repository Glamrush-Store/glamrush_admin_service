<?php

namespace App\Domain\Newsletter\UseCases;

use App\Domain\Newsletter\Actions\BuildNewsletterSubscriberQueryAction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListNewsletterSubscribersUseCase
{
    public function __construct(private BuildNewsletterSubscriberQueryAction $buildQuery) {}

    public function run(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->buildQuery->run($filters)->paginate($perPage);
    }
}
