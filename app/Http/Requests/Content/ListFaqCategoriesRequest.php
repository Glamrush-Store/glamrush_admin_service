<?php

namespace App\Http\Requests\Content;

use App\Http\Requests\ApiRequest;

class ListFaqCategoriesRequest extends ApiRequest
{
    public function rules(): array
    {
        return ['search' => ['sometimes', 'string', 'max:255'], 'is_active' => ['sometimes', 'boolean'], 'per_page' => ['sometimes', 'integer', 'between:1,100']];
    }
}
