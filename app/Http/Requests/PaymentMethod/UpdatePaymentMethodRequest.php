<?php

namespace App\Http\Requests\PaymentMethod;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaymentMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('paymentMethod')?->id;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'code' => ['sometimes', 'required', 'string', Rule::in(['paystack', 'flutterwave', 'pay_on_delivery']), Rule::unique('payment_methods', 'code')->ignore($id)],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'public_config' => ['nullable', 'array'],
        ];
    }
}
