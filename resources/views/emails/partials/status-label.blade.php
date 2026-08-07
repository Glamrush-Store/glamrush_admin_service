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
        'pending' => 'Pending',
        'ready' => 'Ready for dispatch',
        'delivered' => 'Delivered',
        'initialized' => 'Initialized',
    ];

    $title = $labels[$status] ?? str($status)->replace('_', ' ')->title();
@endphp

{{ $title }}
