<?php

namespace App\Http\Requests\Storefront;

use App\Http\Requests\ApiRequest;

class ReorderStorefrontSectionsRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'section_ids' => ['required', 'array', 'min:1'],
            'section_ids.*' => ['required', 'string', 'distinct', 'exists:storefront_homepage_sections,id'],
        ];
    }
}
