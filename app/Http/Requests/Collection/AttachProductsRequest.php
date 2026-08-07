<?php

namespace App\Http\Requests\Collection;

use Illuminate\Foundation\Http\FormRequest;

class AttachProductsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'products' => ['required', 'array', 'min:1'],
            'products.*.id' => ['required', 'string', 'exists:products,id'],
            'products.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
