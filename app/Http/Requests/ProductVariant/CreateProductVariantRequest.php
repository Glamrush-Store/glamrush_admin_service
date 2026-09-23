<?php

namespace App\Http\Requests\ProductVariant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateProductVariantRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'product_id' => ['prohibited'],
            'sku' => ['prohibited'],
            'reserved_quantity' => ['prohibited'],

            'price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'sale_starts_at' => ['nullable', 'date'],
            'sale_ends_at' => ['nullable', 'date', 'after:sale_starts_at'],
            'manage_stock' => ['sometimes', 'boolean'],
            'stock_quantity' => ['sometimes', 'integer', 'min:0'],
            'in_stock' => ['sometimes', 'boolean'],
            'attributes' => ['required', 'array', 'min:1'],
            'attributes.*' => ['required', 'array:type,value'],
            'attributes.*.type' => ['required', 'string', 'max:100', 'distinct:strict'],
            'attributes.*.value' => ['required', 'string', 'max:255'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'status' => ['sometimes', Rule::in(['active', 'disabled'])],
            'is_default' => ['sometimes', 'boolean'],
            'photos' => ['sometimes', 'array', 'max:2'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}
