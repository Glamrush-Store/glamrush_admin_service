<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Requests\ProductVariant;

use Illuminate\Foundation\Http\FormRequest;

class DeleteProductVariantRequest extends FormRequest
{
    public function rules(): array
    {
        return [];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $variant = $this->route('variant');
            $product = $variant->product;

            if (
                $variant->is_default &&
                $product->variants()->count() === 1
            ) {
                $validator->errors()->add(
                    'variant',
                    'Cannot delete the only default variant of a product.'
                );
            }
        });
    }
}
