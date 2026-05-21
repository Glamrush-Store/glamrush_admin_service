<?php

namespace App\Domain\Order\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

final class OrderShippingStatusChangedEvent implements ShouldDispatchAfterCommit
{
    public function __construct(
        public readonly string $orderId,
        public readonly string $shipmentId,
        public readonly string $oldStatus,
        public readonly string $newStatus,
    ) {
    }
}
