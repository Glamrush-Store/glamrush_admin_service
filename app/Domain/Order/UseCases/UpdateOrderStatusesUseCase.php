<?php

namespace App\Domain\Order\UseCases;

use App\Domain\Order\Events\OrderPaymentStatusChangedEvent;
use App\Domain\Order\Events\OrderShippingStatusChangedEvent;
use App\Exceptions\BusinessException;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class UpdateOrderStatusesUseCase
{
    public function run(Order $order, array $data): Order
    {
        return DB::transaction(function () use ($order, $data) {
            if (array_key_exists('shipping_status', $data)) {
                $shipment = $order->shipment()->first();

                if (! $shipment) {
                    throw BusinessException::invalidOperation('Order has no shipment record to update.');
                }

                $oldShippingStatus = (string) $shipment->status;
                $newShippingStatus = (string) $data['shipping_status'];
                $shipmentUpdates = ['status' => $newShippingStatus];

                if ($newShippingStatus === 'shipped' && is_null($shipment->shipped_at)) {
                    $shipmentUpdates['shipped_at'] = now();
                }

                if ($newShippingStatus === 'delivered' && is_null($shipment->delivered_at)) {
                    $shipmentUpdates['delivered_at'] = now();
                }

                if ($oldShippingStatus !== $newShippingStatus) {
                    $shipment->update($shipmentUpdates);

                    event(new OrderShippingStatusChangedEvent(
                        $order->id,
                        $shipment->id,
                        $oldShippingStatus,
                        $newShippingStatus,
                    ));
                }
            }

            if (array_key_exists('payment_status', $data)) {
                $payment = $order->latestPayment()->first();

                if (! $payment) {
                    throw BusinessException::invalidOperation('Order has no payment record to update.');
                }

                $oldPaymentStatus = (string) $payment->status;
                $newPaymentStatus = (string) $data['payment_status'];
                $paymentUpdates = ['status' => $newPaymentStatus];

                if ($newPaymentStatus === 'paid' && is_null($payment->paid_at)) {
                    $paymentUpdates['paid_at'] = now();
                }

                if ($newPaymentStatus === 'failed' && is_null($payment->failed_at)) {
                    $paymentUpdates['failed_at'] = now();
                }

                if ($oldPaymentStatus !== $newPaymentStatus) {
                    $payment->update($paymentUpdates);

                    event(new OrderPaymentStatusChangedEvent(
                        $order->id,
                        $payment->id,
                        $oldPaymentStatus,
                        $newPaymentStatus,
                    ));
                }
            }

            return $order->fresh()->load([
                'customer:id,name,email,phone,email_verified_at,created_at,updated_at',
                'items',
                'shippingRate.method:id,name,code',
                'shippingRate.zone:id,name',
                'shipment.method:id,name,code',
                'shipment.zone:id,name',
                'latestPayment.paymentMethod:id,name,code,description',
                'latestPayment.transactions',
            ]);
        });
    }
}
