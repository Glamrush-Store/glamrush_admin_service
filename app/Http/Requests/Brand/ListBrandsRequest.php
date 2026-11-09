<?php

namespace App\Http\Requests\Brand;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListBrandsRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $normalized = [];

        if (! $this->has('sort_by') && $this->filled('sort')) {
            $normalized['sort_by'] = $this->input('sort');
        }

        if (! $this->has('sort_dir') && $this->filled('direction')) {
            $normalized['sort_dir'] = $this->input('direction');
        }

        if ($this->has('is_active')) {
            $isActive = filter_var($this->input('is_active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            if ($isActive !== null) {
                $normalized['is_active'] = $isActive;
            }
        }

        $this->merge($normalized);
    }

    public function rules(): array
    {
        return [
            'search' => ['sometimes', 'nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_by' => ['sometimes', Rule::in([
                'name',
                'code',
                'slug',
                'is_active',
                'sort_order',
                'created_at',
                'updated_at',
            ])],
            'sort_dir' => ['sometimes', Rule::in(['asc', 'desc'])],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
