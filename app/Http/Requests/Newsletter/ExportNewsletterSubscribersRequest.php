<?php

namespace App\Http\Requests\Newsletter;

use App\Http\Requests\ApiRequest;

class ExportNewsletterSubscribersRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'source' => ['sometimes', 'nullable', 'string', 'max:100'],
            'confirmed_from' => ['sometimes', 'nullable', 'date'],
            'confirmed_to' => ['sometimes', 'nullable', 'date', 'after_or_equal:confirmed_from'],
        ];
    }
}
