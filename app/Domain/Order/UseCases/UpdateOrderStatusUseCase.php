<?php

namespace App\Domain\Order\UseCases;

use App\Domain\Order\Actions\ChangeOrderStatusAction;
use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Events\OrderStatusChangedEvent;
use App\Models\Order;

class UpdateOrderStatusUseCase
{
    public function __construct(private ChangeOrderStatusAction $changeStatus) {}

    public function run(Order $order, string $status): Order
    {
        $oldStatus = $order->status instanceof OrderStatus
            ? $order->status->value
            : (string) $order->status;

        $order = $this->changeStatus->execute($order, OrderStatus::from($status));

        $newStatus = $order->status instanceof OrderStatus
            ? $order->status->value
            : (string) $order->status;

        if ($oldStatus !== $newStatus) {
            event(new OrderStatusChangedEvent($order->id, $oldStatus, $newStatus));
        }

        return $order->load([
            'customer:id,name,email,phone,email_verified_at,created_at,updated_at',
            'items',
            'shippingRate.method:id,name,code',
            'shippingRate.zone:id,name',
            'shipment.method:id,name,code',
            'shipment.zone:id,name',
            'latestPayment.paymentMethod:id,name,code,description',
            'latestPayment.transactions',
        ]);
    }
}
