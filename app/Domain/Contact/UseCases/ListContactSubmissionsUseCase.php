<?php

namespace App\Domain\Contact\UseCases;

use App\Domain\Contact\Actions\BuildContactSubmissionQueryAction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListContactSubmissionsUseCase
{
    public function __construct(private BuildContactSubmissionQueryAction $buildQuery) {}

    public function run(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->buildQuery->run($filters)->paginate($perPage);
    }
}
