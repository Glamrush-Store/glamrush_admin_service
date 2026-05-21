<?php

namespace App\Domain\Order\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

final class OrderPaymentStatusChangedEvent implements ShouldDispatchAfterCommit
{
    public function __construct(
        public readonly string $orderId,
        public readonly string $paymentId,
        public readonly string $oldStatus,
        public readonly string $newStatus,
    ) {
    }
}
