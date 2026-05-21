<?php

namespace App\Listeners\Order;

use App\Domain\Order\Events\OrderShippingStatusChangedEvent;
use App\Mail\Orders\OrderShippingStatusChangedMail;
use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

final class SendOrderShippingStatusChangedEmail implements ShouldQueue
{
    public function handle(OrderShippingStatusChangedEvent $event): void
    {
        $order = Order::query()
            ->with(['customer', 'items'])
            ->whereKey($event->orderId)
            ->first();

        $shipment = Shipment::query()
            ->with(['method', 'zone'])
            ->whereKey($event->shipmentId)
            ->first();

        if ($order === null || $shipment === null) {
            return;
        }

        $email = $order->shipping_address['email'] ?? $order->customer?->email;
        $name = $order->shipping_address['full_name'] ?? $order->customer?->name;

        if (! $email) {
            return;
        }

        Mail::to($email, $name)->send(
            new OrderShippingStatusChangedMail($order, $shipment, $event->oldStatus, $event->newStatus)
        );
    }
}
