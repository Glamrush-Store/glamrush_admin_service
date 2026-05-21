@if ($order->items->isNotEmpty())
@component('mail::table')
| Item | Qty | Total |
|:---|:---:|---:|
@foreach ($order->items as $item)
| {{ $item->product_name ?? 'Item' }} @if($item->sku) <br><small>{{ $item->sku }}</small> @endif | {{ $item->quantity }} | {{ $order->currency ?? 'NGN' }} {{ number_format((float) $item->total, 2) }} |
@endforeach
@endcomponent
@endif
