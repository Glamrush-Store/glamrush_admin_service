<?php

namespace App\Swagger\Discount;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'DiscountTargetInput', required: ['target_type', 'target_id', 'mode'], properties: [
    new OA\Property(property: 'target_type', type: 'string', enum: ['product', 'product_variant', 'category', 'brand', 'collection']),
    new OA\Property(property: 'target_id', type: 'string'), new OA\Property(property: 'mode', type: 'string', enum: ['include', 'exclude']),
])]
#[OA\Schema(schema: 'DiscountCodeInput', required: ['code', 'name', 'type', 'is_active', 'first_order_only', 'applies_to_sale_items', 'applies_to_all_storefronts'], properties: [
    new OA\Property(property: 'code', type: 'string', example: 'WELCOME10'), new OA\Property(property: 'name', type: 'string'),
    new OA\Property(property: 'description', type: 'string', nullable: true), new OA\Property(property: 'type', type: 'string', enum: ['percentage', 'fixed_amount', 'free_shipping']),
    new OA\Property(property: 'value', type: 'string', nullable: true, example: '10.00'), new OA\Property(property: 'currency', type: 'string', nullable: true, example: 'NGN'),
    new OA\Property(property: 'maximum_discount_amount', type: 'string', nullable: true), new OA\Property(property: 'minimum_subtotal', type: 'string', nullable: true),
    new OA\Property(property: 'starts_at', type: 'string', format: 'date-time', nullable: true), new OA\Property(property: 'ends_at', type: 'string', format: 'date-time', nullable: true),
    new OA\Property(property: 'is_active', type: 'boolean'), new OA\Property(property: 'total_usage_limit', type: 'integer', nullable: true),
    new OA\Property(property: 'per_customer_usage_limit', type: 'integer', nullable: true), new OA\Property(property: 'first_order_only', type: 'boolean'),
    new OA\Property(property: 'applies_to_sale_items', type: 'boolean'), new OA\Property(property: 'applies_to_all_storefronts', type: 'boolean'),
    new OA\Property(property: 'storefront_ids', type: 'array', items: new OA\Items(type: 'string')),
    new OA\Property(property: 'targets', type: 'array', items: new OA\Items(ref: '#/components/schemas/DiscountTargetInput')),
])]
#[OA\Get(path: '/discount-codes', tags: ['Discount Codes'], security: [['sanctum' => []]], parameters: [
    new OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string')), new OA\Parameter(name: 'type', in: 'query', schema: new OA\Schema(type: 'string')),
    new OA\Parameter(name: 'state', in: 'query', schema: new OA\Schema(type: 'string', enum: ['draft', 'scheduled', 'active', 'expired', 'inactive'])),
    new OA\Parameter(name: 'storefront_id', in: 'query', schema: new OA\Schema(type: 'string')), new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', maximum: 100)),
], responses: [new OA\Response(response: 200, description: 'Paginated discount codes'), new OA\Response(response: 401, description: 'Unauthenticated'), new OA\Response(response: 403, description: 'Forbidden')])]
#[OA\Post(path: '/discount-codes', tags: ['Discount Codes'], security: [['sanctum' => []]], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/DiscountCodeInput')), responses: [new OA\Response(response: 201, description: 'Created'), new OA\Response(response: 422, description: 'Validation error')])]
#[OA\Get(path: '/discount-codes/{discountCode}', tags: ['Discount Codes'], security: [['sanctum' => []]], parameters: [new OA\Parameter(name: 'discountCode', in: 'path', required: true, schema: new OA\Schema(type: 'string'))], responses: [new OA\Response(response: 200, description: 'Discount code details'), new OA\Response(response: 404, description: 'Not found')])]
#[OA\Patch(path: '/discount-codes/{discountCode}', tags: ['Discount Codes'], security: [['sanctum' => []]], parameters: [new OA\Parameter(name: 'discountCode', in: 'path', required: true, schema: new OA\Schema(type: 'string'))], requestBody: new OA\RequestBody(content: new OA\JsonContent(ref: '#/components/schemas/DiscountCodeInput')), responses: [new OA\Response(response: 200, description: 'Updated'), new OA\Response(response: 409, description: 'Immutable after redemption')])]
#[OA\Post(path: '/discount-codes/{discountCode}/activate', tags: ['Discount Codes'], security: [['sanctum' => []]], parameters: [new OA\Parameter(name: 'discountCode', in: 'path', required: true, schema: new OA\Schema(type: 'string'))], responses: [new OA\Response(response: 200, description: 'Activated')])]
#[OA\Post(path: '/discount-codes/{discountCode}/deactivate', tags: ['Discount Codes'], security: [['sanctum' => []]], parameters: [new OA\Parameter(name: 'discountCode', in: 'path', required: true, schema: new OA\Schema(type: 'string'))], responses: [new OA\Response(response: 200, description: 'Deactivated')])]
#[OA\Post(path: '/discount-codes/{discountCode}/duplicate', tags: ['Discount Codes'], security: [['sanctum' => []]], parameters: [new OA\Parameter(name: 'discountCode', in: 'path', required: true, schema: new OA\Schema(type: 'string'))], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['code'], properties: [new OA\Property(property: 'code', type: 'string')])), responses: [new OA\Response(response: 201, description: 'Duplicated')])]
class DiscountCodes {}
