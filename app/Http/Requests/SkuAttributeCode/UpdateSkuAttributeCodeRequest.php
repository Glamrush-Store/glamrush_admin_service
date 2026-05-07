<?php
/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Requests\SkuAttributeCode;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSkuAttributeCodeRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'type' => ['sometimes', 'string', 'max:255'],
            'value' => ['sometimes', 'string', 'max:255'],
            'code' => [
                'sometimes',
                'string',
                'max:50',
                'unique:sku_attribute_codes,code,' . $this->route('skuAttributeCode')->id
            ],
            'is_active' => ['boolean'],
        ];
    }

}
