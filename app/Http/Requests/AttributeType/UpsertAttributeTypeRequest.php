<?php

namespace App\Http\Requests\AttributeType;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class UpsertAttributeTypeRequest extends ApiRequest
{
    public function rules(): array
    {
        $creating = $this->isMethod('post');

        return [
            'category' => ['sometimes', 'nullable', 'string', 'max:255'],
            'value' => [
                $creating ? 'required' : 'sometimes',
                'string',
                'max:255',
                'regex:/^[a-z][a-z0-9_]*$/',
                Rule::unique('attribute_types', 'value')->ignore($this->route('attributeType')?->id),
            ],
            'label' => [$creating ? 'required' : 'sometimes', 'string', 'max:255'],
            'display_type' => [$creating ? 'required' : 'sometimes', 'string', 'max:255'],
        ];
    }
}
