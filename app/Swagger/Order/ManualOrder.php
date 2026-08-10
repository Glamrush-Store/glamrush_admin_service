<?php

namespace App\Swagger\Order;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'ManualOrderItemInput', required: ['product_variant_id', 'quantity', 'unit_price'], properties: [
    new OA\Property(property: 'product_variant_id', type: 'string'),
    new OA\Property(property: 'quantity', type: 'integer', minimum: 1),
    new OA\Property(property: 'unit_price', type: 'string', example: '12500.00'),
])]
#[OA\Schema(schema: 'ManualOrderAddressInput', required: ['name', 'phone', 'address_line_1', 'city', 'state', 'country'], properties: [
    new OA\Property(property: 'name', type: 'string'), new OA\Property(property: 'email', type: 'string', format: 'email', nullable: true),
    new OA\Property(property: 'phone', type: 'string'), new OA\Property(property: 'address_line_1', type: 'string'),
    new OA\Property(property: 'address_line_2', type: 'string', nullable: true), new OA\Property(property: 'city', type: 'string'),
    new OA\Property(property: 'state', type: 'string'), new OA\Property(property: 'country', type: 'string', example: 'NG'),
    new OA\Property(property: 'postal_code', type: 'string', nullable: true),
])]
#[OA\Schema(schema: 'ManualOrderInput', required: ['items', 'payment_method_id', 'shipping_method_id', 'shipping_zone_id', 'shipping_address'], properties: [
    new OA\Property(property: 'customer_id', type: 'integer', nullable: true),
    new OA\Property(property: 'items', type: 'array', items: new OA\Items(ref: '#/components/schemas/ManualOrderItemInput')),
    new OA\Property(property: 'currency', type: 'string', default: 'NGN'), new OA\Property(property: 'payment_method_id', type: 'string'),
    new OA\Property(property: 'transaction_reference', type: 'string', nullable: true), new OA\Property(property: 'shipping_method_id', type: 'string'),
    new OA\Property(property: 'shipping_zone_id', type: 'string'), new OA\Property(property: 'shipping_rate_id', type: 'string', nullable: true),
    new OA\Property(property: 'shipping_amount', type: 'string', nullable: true),
    new OA\Property(property: 'shipping_address', ref: '#/components/schemas/ManualOrderAddressInput'),
    new OA\Property(property: 'billing_address', ref: '#/components/schemas/ManualOrderAddressInput', nullable: true),
    new OA\Property(property: 'order_status', type: 'string', enum: ['paid', 'processing', 'shipped', 'completed'], default: 'completed'),
    new OA\Property(property: 'shipment_status', type: 'string', enum: ['pending', 'ready', 'shipped', 'delivered'], default: 'delivered'),
    new OA\Property(property: 'carrier', type: 'string', nullable: true), new OA\Property(property: 'tracking_number', type: 'string', nullable: true),
    new OA\Property(property: 'placed_at', type: 'string', format: 'date-time', nullable: true),
])]
#[OA\Post(
    path: '/orders/manual',
    summary: 'Record a paid offline sale',
    tags: ['Orders'],
    security: [['sanctum' => []]],
    parameters: [new OA\Parameter(name: 'Idempotency-Key', in: 'header', required: true, schema: new OA\Schema(type: 'string', maxLength: 100))],
    requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/ManualOrderInput')),
    responses: [
        new OA\Response(response: 201, description: 'Manual order created'), new OA\Response(response: 200, description: 'Idempotent replay'),
        new OA\Response(response: 409, description: 'Stock, transaction reference, or idempotency conflict'),
        new OA\Response(response: 422, description: 'Validation error'), new OA\Response(response: 401, description: 'Unauthenticated'),
        new OA\Response(response: 403, description: 'Forbidden'),
    ]
)]
class ManualOrder {}
