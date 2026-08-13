<?php

namespace App\Http\Requests\Setting;

use App\Http\Requests\ApiRequest;

class ListSettingsRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'category_id' => ['nullable', 'string', 'exists:setting_categories,id'],
            'category' => ['nullable', 'string', 'exists:setting_categories,slug'],
            'search' => ['nullable', 'string', 'max:255'],
            'is_public' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
