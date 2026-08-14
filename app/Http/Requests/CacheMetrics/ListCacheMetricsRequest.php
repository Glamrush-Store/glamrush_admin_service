<?php

namespace App\Http\Requests\CacheMetrics;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class ListCacheMetricsRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'service' => ['nullable', 'string', 'max:80'],
            'area' => ['nullable', 'string', 'max:80'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'per_page' => ['nullable', 'integer', 'between:1,500'],
            'sort_dir' => ['nullable', Rule::in(['asc', 'desc'])],
        ];
    }
}
