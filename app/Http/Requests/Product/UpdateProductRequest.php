<?php

/*
 * (c) 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $pricing = $this->input('pricing');

        if (is_array($pricing)) {
            $pricingFields = [];

            foreach (['price', 'sale_price', 'sale_starts_at', 'sale_ends_at'] as $field) {
                if (! $this->exists($field) && array_key_exists($field, $pricing)) {
                    $pricingFields[$field] = $pricing[$field];
                }
            }

            $this->merge($pricingFields);
        }

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
            'name' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', Rule::in(['simple', 'variable', 'digital', 'service'])],
            'status' => ['sometimes', Rule::in(['published', 'draft', 'archived'])],
            'brand_id' => ['sometimes', 'nullable', 'string', 'exists:brands,id'],
            'vendor_id' => ['sometimes', 'nullable', 'string', 'exists:vendors,id'],
            'short_description' => ['sometimes', 'nullable', 'string'],
            'description' => ['sometimes', 'nullable', 'string'],
            'published_at' => ['sometimes', 'nullable', 'date'],
            'meta_title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'meta_description' => ['sometimes', 'nullable', 'string'],
            'price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'sale_price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'sale_starts_at' => ['sometimes', 'nullable', 'date'],
            'sale_ends_at' => ['sometimes', 'nullable', 'date'],
            'manage_stock' => ['sometimes', 'boolean'],
            'stock_quantity' => ['sometimes', 'integer', 'min:0'],
            'in_stock' => ['sometimes', 'boolean'],
            'is_featured' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'category_ids' => ['sometimes', 'array', 'min:1'],
            'category_ids.*' => ['required', 'string', 'distinct', 'exists:categories,id'],
            'primary_category_id' => ['required_with:category_ids', 'string', 'exists:categories,id'],
            'category_sequences' => ['sometimes', 'array'],
            'category_sequences.*' => ['integer', 'min:0'],
            'photos' => ['sometimes', 'array'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
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
