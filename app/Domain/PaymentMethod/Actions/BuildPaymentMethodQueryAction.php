<?php

namespace App\Domain\PaymentMethod\Actions;

use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Builder;

class BuildPaymentMethodQueryAction
{
    public function run(array $filters): Builder
    {
        return PaymentMethod::query()
            ->when($filters['name'] ?? null, fn(Builder $q, $v) => $q->where('name', 'like', "%{$v}%"))
            ->when($filters['code'] ?? null, fn(Builder $q, $v) => $q->where('code', $v))
            ->when(isset($filters['is_active']), fn(Builder $q) => $q->where('is_active', $filters['is_active']))
            ->orderBy('sort_order');
    }
}
