<?php

namespace App\Http\Requests\Order;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CreateManualOrderRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'idempotency_key' => $this->input('idempotency_key', $this->header('Idempotency-Key')),
            'currency' => strtoupper((string) $this->input('currency', 'NGN')),
        ]);
    }

    public function rules(): array
    {
        $address = [
            'name', 'email', 'phone', 'address_line_1', 'address_line_2',
            'city', 'state', 'country', 'postal_code',
        ];

        return [
            'idempotency_key' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z0-9._:-]+$/'],
            'customer_id' => ['nullable', 'integer', Rule::exists('customer_accounts', 'id')->whereNull('deleted_at')],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.product_variant_id' => ['required', 'string', 'distinct', Rule::exists('product_variants', 'id')],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:65535'],
            'items.*.unit_price' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999.99'],
            'currency' => ['required', Rule::in(['NGN'])],
            'payment_method_id' => ['required', 'string', Rule::exists('payment_methods', 'id')->where('is_active', true)],
            'transaction_reference' => ['nullable', 'string', 'max:191'],
            'shipping_method_id' => ['required', 'string', Rule::exists('shipping_methods', 'id')->where('is_active', true)],
            'shipping_zone_id' => ['required', 'string', Rule::exists('shipping_zones', 'id')->where('is_active', true)],
            'shipping_rate_id' => ['nullable', 'string', Rule::exists('shipping_rates', 'id')->where('is_active', true)],
            'shipping_amount' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999.99'],
            'shipping_address' => ['required', 'array:'.implode(',', $address)],
            'shipping_address.name' => ['required', 'string', 'max:120'],
            'shipping_address.email' => ['nullable', 'email', 'max:191'],
            'shipping_address.phone' => ['required', 'string', 'max:40'],
            'shipping_address.address_line_1' => ['required', 'string', 'max:255'],
            'shipping_address.address_line_2' => ['nullable', 'string', 'max:255'],
            'shipping_address.city' => ['required', 'string', 'max:120'],
            'shipping_address.state' => ['required', 'string', 'max:120'],
            'shipping_address.country' => ['required', 'string', 'size:2'],
            'shipping_address.postal_code' => ['nullable', 'string', 'max:30'],
            'billing_address' => ['nullable', 'array:'.implode(',', $address)],
            'billing_address.name' => ['required_with:billing_address', 'string', 'max:120'],
            'billing_address.email' => ['nullable', 'email', 'max:191'],
            'billing_address.phone' => ['required_with:billing_address', 'string', 'max:40'],
            'billing_address.address_line_1' => ['required_with:billing_address', 'string', 'max:255'],
            'billing_address.address_line_2' => ['nullable', 'string', 'max:255'],
            'billing_address.city' => ['required_with:billing_address', 'string', 'max:120'],
            'billing_address.state' => ['required_with:billing_address', 'string', 'max:120'],
            'billing_address.country' => ['required_with:billing_address', 'string', 'size:2'],
            'billing_address.postal_code' => ['nullable', 'string', 'max:30'],
            'order_status' => ['sometimes', Rule::in(['paid', 'processing', 'shipped', 'completed'])],
            'shipment_status' => ['sometimes', Rule::in(['pending', 'ready', 'shipped', 'delivered'])],
            'carrier' => ['nullable', 'string', 'max:120'],
            'tracking_number' => ['nullable', 'string', 'max:191'],
            'placed_at' => ['nullable', 'date', 'before_or_equal:now'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $rateId = $this->input('shipping_rate_id');

            if ($rateId && ! $validator->errors()->has('shipping_rate_id')) {
                $matches = \App\Models\ShippingRate::query()
                    ->whereKey($rateId)
                    ->where('shipping_method_id', $this->input('shipping_method_id'))
                    ->where('shipping_zone_id', $this->input('shipping_zone_id'))
                    ->exists();

                if (! $matches) {
                    $validator->errors()->add('shipping_rate_id', 'The shipping rate must belong to the selected shipping method and zone.');
                }
            }

            $orderStatus = $this->input('order_status', 'completed');
            $shipmentStatus = $this->input('shipment_status', 'delivered');

            if ($orderStatus === 'completed' && $shipmentStatus !== 'delivered') {
                $validator->errors()->add('shipment_status', 'A completed order must have a delivered shipment.');
            }

            if ($orderStatus === 'shipped' && ! in_array($shipmentStatus, ['shipped', 'delivered'], true)) {
                $validator->errors()->add('shipment_status', 'A shipped order must have a shipped or delivered shipment.');
            }
        }];
    }
}
