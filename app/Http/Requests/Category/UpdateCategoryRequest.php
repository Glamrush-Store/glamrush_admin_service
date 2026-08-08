<?php

namespace App\Http\Requests\Category;

use App\Http\Requests\ApiRequest;

class UpdateCategoryRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'announcement_primary_text' => ['sometimes', 'nullable', 'string', 'max:160'],
            'announcement_secondary_text' => ['sometimes', 'nullable', 'string', 'max:160'],
        ];
    }
}
