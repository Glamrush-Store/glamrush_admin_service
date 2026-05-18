<?php

namespace App\Domain\Order\UseCases;

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

                $shipmentUpdates = ['status' => $data['shipping_status']];

                if ($data['shipping_status'] === 'shipped' && is_null($shipment->shipped_at)) {
                    $shipmentUpdates['shipped_at'] = now();
                }

                if ($data['shipping_status'] === 'delivered' && is_null($shipment->delivered_at)) {
                    $shipmentUpdates['delivered_at'] = now();
                }

                $shipment->update($shipmentUpdates);
            }

            if (array_key_exists('payment_status', $data)) {
                $payment = $order->latestPayment()->first();

                if (! $payment) {
                    throw BusinessException::invalidOperation('Order has no payment record to update.');
                }

                $paymentUpdates = ['status' => $data['payment_status']];

                if ($data['payment_status'] === 'paid' && is_null($payment->paid_at)) {
                    $paymentUpdates['paid_at'] = now();
                }

                if ($data['payment_status'] === 'failed' && is_null($payment->failed_at)) {
                    $paymentUpdates['failed_at'] = now();
                }

                $payment->update($paymentUpdates);
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
