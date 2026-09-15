<?php

namespace App\Http\Requests\AttributeType;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class ListAttributeTypesRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'between:1,100'],
            'search' => ['sometimes', 'nullable', 'string', 'max:255'],
            'category' => ['sometimes', 'nullable', 'string', 'max:255'],
            'sort_by' => ['sometimes', Rule::in(['id', 'category', 'value', 'label', 'display_type', 'created_at', 'updated_at'])],
            'sort_dir' => ['sometimes', Rule::in(['asc', 'desc'])],
        ];
    }
}
