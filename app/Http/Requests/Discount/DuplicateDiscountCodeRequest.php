<?php

namespace App\Http\Requests\Discount;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class DuplicateDiscountCodeRequest extends ApiRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['code' => strtoupper(trim((string) $this->input('code')))]);
    }

    public function rules(): array
    {
        return ['code' => ['required', 'string', 'max:64', 'regex:/^[A-Z0-9_-]+$/', Rule::unique('discount_codes', 'code')]];
    }
}
