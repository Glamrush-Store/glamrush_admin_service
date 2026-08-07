@component('mail::message')
@php
    $labels = [
        'pending_payment' => 'Pending payment',
        'pending_on_delivery' => 'Pending on delivery',
        'paid' => 'Paid',
        'processing' => 'Processing',
        'shipped' => 'Shipped',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        'failed' => 'Failed',
        'refunded' => 'Refunded',
    ];

    $name = $order->shipping_address['full_name'] ?? $order->customer?->name ?? 'there';
    $oldStatusLabel = $labels[$oldStatus] ?? str($oldStatus)->replace('_', ' ')->title();
    $newStatusLabel = $labels[$newStatus] ?? str($newStatus)->replace('_', ' ')->title();
@endphp

# Order update

Hi {{ $name }},

Your order **{{ $order->order_number }}** has moved from **{{ $oldStatusLabel }}** to **{{ $newStatusLabel }}**.

@component('mail::panel')
**Current status:** {{ $newStatusLabel }}

@if ($newStatus === 'processing')
We have started preparing your items.
@elseif ($newStatus === 'shipped')
Your package is now on its way.
@elseif ($newStatus === 'completed')
Your order has been completed. Thank you for shopping with us.
@elseif ($newStatus === 'cancelled')
This order has been cancelled. If you paid already, our team will follow the payment/refund process.
@elseif ($newStatus === 'refunded')
A refund has been processed or marked for this order.
@else
We will keep you posted as the order moves forward.
@endif
@endcomponent

@include('emails.partials.order-summary', ['order' => $order])

@include('emails.partials.order-items', ['order' => $order])

Thanks,<br>
{{ config('app.name') }}
@endcomponent
