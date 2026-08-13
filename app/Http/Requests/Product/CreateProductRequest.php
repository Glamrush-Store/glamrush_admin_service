<?php

/*
 * (c) 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateProductRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $categoryIds = $this->input('category_ids');

        if (! is_array($categoryIds) && $this->filled('category_id')) {
            $categoryIds = [$this->input('category_id')];
        }

        if (is_array($categoryIds)) {
            $categoryIds = array_values(array_filter($categoryIds, fn ($id) => is_string($id) && trim($id) !== ''));
        }

        $this->merge([
            'category_ids' => $categoryIds,
            'primary_category_id' => $this->input('primary_category_id') ?: ($categoryIds[0] ?? null),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'type' => ['required', Rule::in(['simple', 'variable', 'digital', 'service'])],
            'status' => ['required', Rule::in(['published', 'draft', 'archived'])],
            'category_ids' => ['required', 'array', 'min:1'],
            'category_ids.*' => ['required', 'string', 'distinct', 'exists:categories,id'],
            'primary_category_id' => ['required', 'string', 'exists:categories,id'],
            'category_sequences' => ['sometimes', 'array'],
            'category_sequences.*' => ['integer', 'min:0'],
            'brand_id' => ['required', 'exists:brands,id'],
            'description' => ['nullable', 'string'],

            'price' => ['required_if:type,simple', 'numeric'],
            'sale_price' => ['nullable', 'numeric'],
            'sale_starts_at' => ['nullable', 'date'],
            'sale_ends_at' => ['nullable', 'date'],
            'manage_stock' => ['required_if:type,simple', 'boolean'],
            'stock_quantity' => ['required_if:type,simple', 'integer'],
            'in_stock' => ['required_if:type,simple', 'boolean'],
            'product_image' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],

            'variants' => [
                'required_unless:type,simple',
                'array',
                function ($attr, $value, $fail) {
                    if ($this->input('type') !== 'simple') {
                        $hasDefault = collect($value)->contains('is_default', true);
                        if (! $hasDefault) {
                            $fail('At least one default variant is required.');
                        }
                    }
                },
            ],
            'variants.*.price' => ['required', 'numeric'],
            'variants.*.is_default' => ['boolean'],
            'variants.*.attributes' => ['array'],
            'variants.*.photos' => ['sometimes', 'array', 'max:2'],
            'variants.*.photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $categoryIds = collect($this->input('category_ids', []))->map(fn ($id) => (string) $id);
            $primaryCategoryId = (string) $this->input('primary_category_id');

            if ($primaryCategoryId !== '' && ! $categoryIds->contains($primaryCategoryId)) {
                $validator->errors()->add('primary_category_id', 'The primary category must be included in category_ids.');
            }
        });
    }
}
