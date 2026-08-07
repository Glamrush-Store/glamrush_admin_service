<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shipping_status' => ['sometimes', Rule::in(['pending', 'ready', 'shipped', 'delivered', 'failed'])],
            'payment_status' => ['sometimes', Rule::in(['pending', 'initialized', 'pending_on_delivery', 'paid', 'failed'])],
        ];
    }
}
