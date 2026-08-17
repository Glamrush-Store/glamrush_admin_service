<?php

namespace App\Http\Requests\Setting;

use App\Http\Requests\ApiRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpsertSettingCategoryRequest extends ApiRequest
{
    protected function prepareForValidation(): void
    {
        $slug = trim((string) $this->input('slug'));

        if ($slug !== '') {
            $this->merge(['slug' => Str::slug($slug)]);
        } elseif ($this->filled('name')) {
            $this->merge(['slug' => Str::slug(trim((string) $this->input('name')))]);
        }
    }

    public function rules(): array
    {
        $category = $this->route('settingCategory');
        $creating = $this->isMethod('post');

        return [
            'name' => [$creating ? 'required' : 'sometimes', 'string', 'max:120'],
            'slug' => [$creating ? 'required' : 'sometimes', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('setting_categories', 'slug')->ignore($category?->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
