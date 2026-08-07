<?php

namespace App\Listeners\Order;

use App\Domain\Order\Events\OrderPaymentStatusChangedEvent;
use App\Mail\Orders\OrderPaymentStatusChangedMail;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

final class SendOrderPaymentStatusChangedEmail implements ShouldQueue
{
    public function handle(OrderPaymentStatusChangedEvent $event): void
    {
        $order = Order::query()
            ->with(['customer', 'items'])
            ->whereKey($event->orderId)
            ->first();

        $payment = Payment::query()
            ->with(['paymentMethod'])
            ->whereKey($event->paymentId)
            ->first();

        if ($order === null || $payment === null) {
            return;
        }

        $email = $order->shipping_address['email'] ?? $order->customer?->email;
        $name = $order->shipping_address['full_name'] ?? $order->customer?->name;

        if (! $email) {
            return;
        }

        Mail::to($email, $name)->send(
            new OrderPaymentStatusChangedMail($order, $payment, $event->oldStatus, $event->newStatus)
        );
    }
}
