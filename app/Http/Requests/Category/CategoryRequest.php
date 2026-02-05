<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function rules(): array
    {
        $categoryId = $this->route('category')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            // Slug is optional because you generate it in the UseCase
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('categories', 'slug')->ignore($categoryId),
            ],

            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id'),
            ],

            'is_active' => [
                'required',
                'boolean',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'meta_title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'meta_description' => [
                'nullable',
                'string',
                'max:255',
            ],

            'meta_keywords' => [
                'nullable',
                'string',
                'max:255',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],

            // Spatie Media image (optional)
            'image' => [
                'sometimes',
                'image',
                'max:2048', // 2MB
            ],
        ];
    }

    public function validated($key = null, $default = null)
    {
        // Never trust client-provided slug unless you explicitly want to
        return collect(parent::validated($key, $default))
            ->except('slug')
            ->toArray();
    }
}
