<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Requests\Brand;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBrandRequest extends FormRequest
{
    public function rules(): array
    {
        // $brand = $this->route('brand');

        return [
            'name' => [
                'sometimes',
                'string',
                'max:255',
            ],

            'is_active' => [
                'sometimes',
                'string',
            ],

            'description' => [
                'sometimes',
                'nullable',
                'string',
            ],

            'meta_title' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],

            'meta_description' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],

            'meta_keywords' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],

            'sort_order' => [
                'sometimes',
                'integer',
                'min:0',
            ],

            'logo' => [
                'sometimes',
                'image',
                'max:2048',
            ],
        ];
    }
}
