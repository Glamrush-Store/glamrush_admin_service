<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Requests\ProductVariant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductVariantRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'product_id' => ['prohibited'],
            'slug' => ['prohibited'],

            'sku' => ['sometimes', 'string'],
            'price' => ['sometimes', 'numeric'],
            'sale_price' => ['nullable', 'numeric'],
            'sale_starts_at' => ['nullable', 'date'],
            'sale_ends_at' => ['nullable', 'date'],
            'manage_stock' => ['boolean'],
            'stock_quantity' => ['nullable', 'integer'],
            'in_stock' => ['boolean'],
            'attributes' => ['nullable', 'array'],
            'sort_order' => ['integer'],
            'status' => ['sometimes', Rule::in(['published', 'draft', 'archived'])],
            'is_default' => ['boolean'],
            'photos' => ['sometimes', 'array', 'max:2'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->boolean('is_default')) {
                $variant = $this->route('variant');

                $hasOtherDefault = $variant->product
                    ->variants()
                    ->where('id', '!=', $variant->id)
                    ->where('is_default', true)
                    ->exists();

                if ($hasOtherDefault) {
                    $validator->errors()->add(
                        'is_default',
                        'Only one default variant is allowed per product.'
                    );
                }
            }
        });
    }
}
