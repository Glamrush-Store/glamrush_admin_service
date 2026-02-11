<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Swagger\Product;

use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/api/products',
    summary: 'Create product',
    security: [['bearerAuth' => []]],
    tags: ['Products'],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['name', 'slug', 'type', 'status'],
            properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'slug', type: 'string'),
                new OA\Property(property: 'type', type: 'string', example: 'variable'),
                new OA\Property(property: 'status', type: 'string', example: 'published'),
                new OA\Property(
                    property: 'variants',
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/ProductVariant')
                ),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: 'Product created',
            content: new OA\JsonContent(ref: '#/components/schemas/Product')
        ),
    ]
)]
class CreateProduct {}
