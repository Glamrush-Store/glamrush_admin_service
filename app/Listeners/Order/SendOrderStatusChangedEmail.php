<?php

namespace App\Listeners\Order;

use App\Domain\Order\Events\OrderStatusChangedEvent;
use App\Mail\Orders\OrderStatusChangedMail;
use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

final class SendOrderStatusChangedEmail implements ShouldQueue
{
    public function handle(OrderStatusChangedEvent $event): void
    {
        $order = Order::query()
            ->with(['customer', 'items', 'latestPayment', 'shipment'])
            ->whereKey($event->orderId)
            ->first();

        if ($order === null) {
            return;
        }

        $email = $order->shipping_address['email'] ?? $order->customer?->email;
        $name = $order->shipping_address['full_name'] ?? $order->customer?->name;

        if (! $email) {
            return;
        }

        Mail::to($email, $name)->send(
            new OrderStatusChangedMail($order, $event->oldStatus, $event->newStatus)
        );
    }
}
