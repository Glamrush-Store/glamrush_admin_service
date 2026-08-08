<?php

namespace App\Http\Requests\Content;

use App\Http\Requests\ApiRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpsertFaqCategoryRequest extends ApiRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('slug')) {
            $this->merge(['slug' => Str::slug(trim((string) $this->input('slug')))]);
        } elseif ($this->isMethod('post') && $this->filled('name')) {
            $this->merge(['slug' => Str::slug(trim((string) $this->input('name')))]);
        }
    }

    public function rules(): array
    {
        $creating = $this->isMethod('post');
        $id = $this->route('faqCategory')?->id;

        return ['name' => [$creating ? 'required' : 'sometimes', 'string', 'max:255'], 'slug' => [$creating ? 'required' : 'sometimes', 'string', 'max:160', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('faq_categories', 'slug')->ignore($id)], 'description' => ['nullable', 'string', 'max:2000'], 'display_order' => ['sometimes', 'integer', 'min:0'], 'is_active' => ['sometimes', 'boolean']];
    }
}
