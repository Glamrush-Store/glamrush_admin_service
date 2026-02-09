<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateProductRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'type' => ['required', Rule::in(['simple', 'variable', 'digital', 'service'])],
            'status' => ['required', Rule::in(['published', 'draft', 'archived'])],
            'category_id' => ['required', 'exists:categories,id'],
            'brand_id' => ['required', 'exists:brands,id'],
            'description' => ['nullable', 'string'],

            'price' => ['required_if:type,simple', 'numeric'],
            'sale_price' => ['required_if:type,simple', 'nullable', 'numeric'],
            'sale_starts_at' => ['required_if:type,simple', 'nullable', 'date'],
            'sale_ends_at' => ['required_if:type,simple', 'nullable', 'date'],
            'manage_stock' => ['required_if:type,simple', 'boolean'],
            'stock_quantity' => ['required_if:type,simple', 'integer'],
            'in_stock' => ['required_if:type,simple', 'boolean'],
            'product_image' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],

            'variants' => ['array', 'required_unless:type,simple'],
            'variants.*.price' => ['required', 'numeric'],
            'variants.*.is_default' => ['boolean'],
            'variants.*.attributes' => ['array'],
            'variants.*.photos' => ['sometimes', 'array', 'max:2'],
            'variants.*.photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],

            // 👇 ensures at least one default variant
            'variants' => [
                function ($attr, $value, $fail) {
                    if (request('type') !== 'simple') {
                        $hasDefault = collect($value)->contains('is_default', true);
                        if (! $hasDefault) {
                            $fail('At least one default variant is required.');
                        }
                    }
                },
            ],
        ];
    }
}
