<?php

namespace App\Domain\Customer\Actions;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder;

class BuildCustomerQueryAction
{
    public function run(array $filters): Builder
    {
        return Customer::query()
            ->when(
                $filters['name'] ?? null,
                fn (Builder $q, string $v) => $q->where('name', 'like', "%{$v}%")
            )
            ->when(
                isset($filters['is_active']),
                fn (Builder $q) => $q->where('is_active', $filters['is_active'])
            )
            ->latest();
    }
}
