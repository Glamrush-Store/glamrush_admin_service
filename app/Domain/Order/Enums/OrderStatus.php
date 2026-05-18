<?php

namespace App\Domain\Order\Enums;

enum OrderStatus: string
{
    case PENDING_PAYMENT = 'pending_payment';
    case PENDING_ON_DELIVERY = 'pending_on_delivery';
    case PAID = 'paid';
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case FAILED = 'failed';
    case REFUNDED = 'refunded';

    public function allowedTransitions(): array
    {
        return match ($this) {
            self::PENDING_PAYMENT => [
                self::PAID,
                self::FAILED,
                self::CANCELLED,
            ],
            self::PENDING_ON_DELIVERY => [
                self::PROCESSING,
                self::CANCELLED,
            ],
            self::PAID => [
                self::PROCESSING,
                self::CANCELLED,
                self::REFUNDED,
            ],
            self::PROCESSING => [
                self::SHIPPED,
                self::CANCELLED,
                self::REFUNDED,
            ],
            self::SHIPPED => [
                self::COMPLETED,
                self::REFUNDED,
            ],
            self::COMPLETED => [
                self::REFUNDED,
            ],
            self::CANCELLED,
            self::FAILED,
            self::REFUNDED => [],
        };
    }

    public function canTransitionTo(self $newStatus): bool
    {
        return in_array($newStatus, $this->allowedTransitions(), true);
    }

    public function isFinal(): bool
    {
        return in_array($this, [
            self::CANCELLED,
            self::FAILED,
            self::REFUNDED,
        ], true);
    }

    public static function values(): array
    {
        return array_map(fn (self $status) => $status->value, self::cases());
    }
}
