@component('mail::message')
@php
    $labels = [
        'pending' => 'Pending',
        'initialized' => 'Initialized',
        'pending_on_delivery' => 'Pending on delivery',
        'paid' => 'Paid',
        'failed' => 'Failed',
    ];

    $name = $order->shipping_address['full_name'] ?? $order->customer?->name ?? 'there';
    $oldStatusLabel = $labels[$oldStatus] ?? str($oldStatus)->replace('_', ' ')->title();
    $newStatusLabel = $labels[$newStatus] ?? str($newStatus)->replace('_', ' ')->title();
@endphp

# Payment update

Hi {{ $name }},

The payment status for order **{{ $order->order_number }}** changed from **{{ $oldStatusLabel }}** to **{{ $newStatusLabel }}**.

@component('mail::panel')
**Payment status:** {{ $newStatusLabel }}

@if ($payment->paymentMethod)
**Payment method:** {{ $payment->paymentMethod->name }}
@endif

@if ($payment->provider)
**Provider:** {{ str($payment->provider)->replace('_', ' ')->title() }}
@endif

@if ($payment->reference)
**Reference:** {{ $payment->reference }}
@endif

@if ($payment->paid_at)
**Paid at:** {{ $payment->paid_at->format('M j, Y g:i A') }}
@endif

@if ($payment->failed_at)
**Failed at:** {{ $payment->failed_at->format('M j, Y g:i A') }}
@endif
@endcomponent

@include('emails.partials.order-summary', ['order' => $order])

Thanks,<br>
{{ config('app.name') }}
@endcomponent
