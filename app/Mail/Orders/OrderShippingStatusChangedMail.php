<?php

namespace App\Mail\Orders;

use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class OrderShippingStatusChangedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Order $order,
        public readonly Shipment $shipment,
        public readonly string $oldStatus,
        public readonly string $newStatus,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Shipping update for order {$this->order->order_number}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.orders.shipping-status-changed',
        );
    }
}
