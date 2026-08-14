<?php

namespace App\Http\Requests\Setting;

use App\Http\Requests\ApiRequest;

class SendTestEmailRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email:rfc', 'max:255'],
            'name' => ['nullable', 'string', 'max:120'],
        ];
    }
}
