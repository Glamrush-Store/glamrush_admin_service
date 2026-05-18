<?php

namespace App\Domain\Collection\Actions;

use App\Models\Collection;
use Illuminate\Database\Eloquent\Builder;

class BuildCollectionQueryAction
{
    public function run(array $filters): Builder
    {
        return Collection::query()
            ->when($filters['is_active'] ?? null, fn ($q, $v) => $q->where('is_active', $v))
            ->when($filters['search'] ?? null, fn ($q, $v) => $q->where('name', 'like', "%{$v}%"))
            ->orderBy(
                $filters['sort_by'] ?? 'sort_order',
                $filters['direction'] ?? 'asc'
            );
    }
}
