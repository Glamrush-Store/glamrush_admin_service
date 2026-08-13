<?php

namespace App\Http\Resources\PaymentTransaction;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $payment = $this->whenLoaded('payment', fn () => $this->payment);
        $order = $payment?->relationLoaded('order') ? $payment->order : null;
        $paymentMethod = $payment?->relationLoaded('paymentMethod') ? $payment->paymentMethod : null;

        return [
            'id' => $this->id,
            'payment_id' => $this->payment_id,
            'event_key' => $this->event_key,
            'type' => $this->type,
            'status' => $this->status,
            'provider_reference' => $this->provider_reference,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'payload' => $this->payload,
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
            'payment' => $payment ? [
                'id' => $payment->id,
                'provider' => $payment->provider,
                'reference' => $payment->reference,
                'provider_reference' => $payment->provider_reference,
                'transaction_id' => $payment->transaction_id,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'status' => $payment->status,
                'paid_at' => optional($payment->paid_at)->toISOString(),
                'failed_at' => optional($payment->failed_at)->toISOString(),
            ] : null,
            'payment_method' => $paymentMethod ? [
                'id' => $paymentMethod->id,
                'name' => $paymentMethod->name,
                'code' => $paymentMethod->code,
            ] : null,
            'order' => $order ? [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'total' => $order->total,
                'currency' => $order->currency,
                'created_at' => optional($order->created_at)->toISOString(),
            ] : null,
        ];
    }
}
