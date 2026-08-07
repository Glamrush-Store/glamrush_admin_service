@php
    $address = $order->shipping_address ?? [];
@endphp

@if (! empty($address))
@component('mail::panel')
**Shipping to:** {{ $address['full_name'] ?? $order->customer?->name ?? 'Customer' }}

@if(! empty($address['phone']))
**Phone:** {{ $address['phone'] }}
@endif

@if(! empty($address['address_line_1']))
{{ $address['address_line_1'] }}
@endif
@if(! empty($address['address_line_2']))
<br>{{ $address['address_line_2'] }}
@endif
@if(! empty($address['city']) || ! empty($address['state']))
<br>{{ trim(($address['city'] ?? '').', '.($address['state'] ?? ''), ', ') }}
@endif
@if(! empty($address['country']))
<br>{{ $address['country'] }}
@endif
@endcomponent
@endif
