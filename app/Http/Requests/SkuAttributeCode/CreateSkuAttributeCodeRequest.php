<?php
/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Requests\SkuAttributeCode;

use Illuminate\Foundation\Http\FormRequest;

class CreateSkuAttributeCodeRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'max:255'],
            'value' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:sku_attribute_codes,code'],
            'is_active' => ['boolean'],
        ];
    }

}
