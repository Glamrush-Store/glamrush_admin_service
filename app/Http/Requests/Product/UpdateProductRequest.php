<?php

/*
 * (c) 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $categoryIds = $this->input('category_ids');

        if (! is_array($categoryIds) && $this->filled('category_id')) {
            $categoryIds = [$this->input('category_id')];
        }

        if (is_array($categoryIds)) {
            $categoryIds = array_values(array_filter($categoryIds, fn ($id) => is_string($id) && trim($id) !== ''));
            $this->merge([
                'category_ids' => $categoryIds,
                'primary_category_id' => $this->input('primary_category_id') ?: ($categoryIds[0] ?? null),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'category_ids' => ['sometimes', 'array', 'min:1'],
            'category_ids.*' => ['required', 'string', 'distinct', 'exists:categories,id'],
            'primary_category_id' => ['required_with:category_ids', 'string', 'exists:categories,id'],
            'category_sequences' => ['sometimes', 'array'],
            'category_sequences.*' => ['integer', 'min:0'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->has('category_ids')) {
                return;
            }

            $categoryIds = collect($this->input('category_ids', []))->map(fn ($id) => (string) $id);
            $primaryCategoryId = (string) $this->input('primary_category_id');

            if ($primaryCategoryId !== '' && ! $categoryIds->contains($primaryCategoryId)) {
                $validator->errors()->add('primary_category_id', 'The primary category must be included in category_ids.');
            }
        });
    }
}
