<?php

namespace App\Domain\Order\Actions;

use App\Domain\Order\Enums\OrderStatus;
use App\Exceptions\BusinessException;
use App\Models\Order;

class ChangeOrderStatusAction
{
    public function execute(Order $order, OrderStatus $newStatus): Order
    {
        $currentStatus = $order->status;

        if (! $currentStatus instanceof OrderStatus) {
            $currentStatus = OrderStatus::from($currentStatus);
        }

        if (! $currentStatus->canTransitionTo($newStatus)) {
            throw BusinessException::invalidOperation(
                "Order status cannot be changed from {$currentStatus->value} to {$newStatus->value}."
            );
        }

        $updates = ['status' => $newStatus];

        if ($newStatus === OrderStatus::PAID && is_null($order->paid_at)) {
            $updates['paid_at'] = now();
        }

        if ($newStatus === OrderStatus::CANCELLED && is_null($order->cancelled_at)) {
            $updates['cancelled_at'] = now();
        }

        $order->update($updates);

        return $order->fresh();
    }
}
