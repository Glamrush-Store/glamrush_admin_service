<?php

namespace App\Http\Requests\Setting;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class UpsertSettingRequest extends ApiRequest
{
    public function rules(): array
    {
        $setting = $this->route('setting');
        $creating = $this->isMethod('post');
        $categoryId = $this->input('setting_category_id', $setting?->setting_category_id);

        return [
            'setting_category_id' => [$creating ? 'required' : 'sometimes', 'string', 'exists:setting_categories,id'],
            'key' => [
                $creating ? 'required' : 'sometimes',
                'string',
                'max:120',
                'regex:/^[a-zA-Z0-9_.-]+$/',
                Rule::unique('settings', 'key')
                    ->where(fn ($query) => $query->where('setting_category_id', $categoryId))
                    ->ignore($setting?->id),
            ],
            'value' => ['nullable'],
            'value_type' => ['sometimes', Rule::in(['string', 'boolean', 'integer', 'decimal', 'array', 'json'])],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_public' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
