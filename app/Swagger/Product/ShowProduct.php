<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Swagger\Product;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/products/{product}',
    summary: 'Get product',
    security: [['bearerAuth' => []]],
    tags: ['Products'],
    parameters: [
        new OA\Parameter(
            name: 'product',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'integer')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Product detail',
            content: new OA\JsonContent(ref: '#/components/schemas/Product')
        ),
    ]
)]
class ShowProduct {}
