@php
    $currency = $order->currency ?? 'NGN';
@endphp

@component('mail::panel')
**Order number:** {{ $order->order_number }}

**Subtotal:** {{ $currency }} {{ number_format((float) $order->subtotal, 2) }}

**Shipping:** {{ $currency }} {{ number_format((float) $order->shipping_amount, 2) }}

**Total:** {{ $currency }} {{ number_format((float) $order->total, 2) }}
@endcomponent
