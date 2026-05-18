<?php

namespace App\Domain\Order\UseCases;

use App\Models\Order;

class ShowOrderUseCase
{
    public function run(Order $order): Order
    {
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
