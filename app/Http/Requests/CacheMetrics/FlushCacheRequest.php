<?php

namespace App\Http\Requests\CacheMetrics;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class FlushCacheRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'service' => ['required', Rule::in(['all', 'admin_service', 'backend_service'])],
            'include_metrics' => ['sometimes', 'boolean'],
            'confirm' => ['accepted'],
        ];
    }
}
