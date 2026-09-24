<?php

namespace App\Domain\Contact\Actions;

use App\Models\ContactSubmission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class BuildContactSubmissionQueryAction
{
    public function run(array $filters): Builder
    {
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_dir'] ?? 'desc';

        return ContactSubmission::query()
            ->with([
                'storefront:id,name,slug',
                'customer:id,name,email,phone',
            ])
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $needle = '%'.mb_strtolower($search).'%';

                $query->where(function (Builder $query) use ($needle): void {
                    foreach (['name', 'email', 'phone', 'subject', 'message'] as $column) {
                        $method = $column === 'name' ? 'whereRaw' : 'orWhereRaw';
                        $query->{$method}("LOWER({$column}) LIKE ?", [$needle]);
                    }
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['source'] ?? null, fn (Builder $query, string $source) => $query->where('source', $source))
            ->when($filters['storefront_category_id'] ?? null, fn (Builder $query, string $id) => $query->where('storefront_category_id', $id))
            ->when($filters['customer_account_id'] ?? null, fn (Builder $query, int $id) => $query->where('customer_account_id', $id))
            ->when($filters['created_from'] ?? null, fn (Builder $query, string $from) => $query->where('created_at', '>=', $from))
            ->when($filters['created_to'] ?? null, fn (Builder $query, string $to) => $query->where('created_at', '<=', $this->inclusiveEndDate($to)))
            ->when($filters['resolved_from'] ?? null, fn (Builder $query, string $from) => $query->where('resolved_at', '>=', $from))
            ->when($filters['resolved_to'] ?? null, fn (Builder $query, string $to) => $query->where('resolved_at', '<=', $this->inclusiveEndDate($to)))
            ->orderBy($sortBy, $sortDirection)
            ->orderBy('id');
    }

    private function inclusiveEndDate(string $value): string
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)
            ? Carbon::parse($value)->endOfDay()->toDateTimeString()
            : $value;
    }
}
