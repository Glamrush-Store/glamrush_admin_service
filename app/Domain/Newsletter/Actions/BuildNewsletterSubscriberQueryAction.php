<?php

namespace App\Domain\Newsletter\Actions;

use App\Models\NewsletterSubscriber;
use Illuminate\Database\Eloquent\Builder;

class BuildNewsletterSubscriberQueryAction
{
    public function run(array $filters): Builder
    {
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_dir'] ?? 'desc';

        return NewsletterSubscriber::query()
            ->searchEmail($filters['search'] ?? null)
            ->withStatus($filters['status'] ?? null)
            ->fromSource($filters['source'] ?? null)
            ->confirmedBetween($filters['confirmed_from'] ?? null, $filters['confirmed_to'] ?? null)
            ->createdBetween($filters['created_from'] ?? null, $filters['created_to'] ?? null)
            ->orderBy($sortBy, $sortDirection)
            ->orderBy('id');
    }
}
