@component('mail::message')
@php
    $labels = [
        'pending' => 'Pending',
        'ready' => 'Ready for dispatch',
        'shipped' => 'Shipped',
        'delivered' => 'Delivered',
        'failed' => 'Delivery issue',
    ];

    $name = $order->shipping_address['full_name'] ?? $order->customer?->name ?? 'there';
    $oldStatusLabel = $labels[$oldStatus] ?? str($oldStatus)->replace('_', ' ')->title();
    $newStatusLabel = $labels[$newStatus] ?? str($newStatus)->replace('_', ' ')->title();
@endphp

# Shipping update

Hi {{ $name }},

The shipping status for order **{{ $order->order_number }}** changed from **{{ $oldStatusLabel }}** to **{{ $newStatusLabel }}**.

@component('mail::panel')
**Shipping status:** {{ $newStatusLabel }}

@if ($shipment->carrier)
**Carrier:** {{ $shipment->carrier }}
@endif

@if ($shipment->tracking_number)
**Tracking number:** {{ $shipment->tracking_number }}
@endif

@if ($shipment->method)
**Method:** {{ $shipment->method->name }}
@endif

@if ($shipment->shipped_at)
**Shipped at:** {{ $shipment->shipped_at->format('M j, Y g:i A') }}
@endif

@if ($shipment->delivered_at)
**Delivered at:** {{ $shipment->delivered_at->format('M j, Y g:i A') }}
@endif
@endcomponent

@include('emails.partials.shipping-address', ['order' => $order])

@include('emails.partials.order-summary', ['order' => $order])

Thanks,<br>
{{ config('app.name') }}
@endcomponent
