<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Swagger\Product;

use OpenApi\Attributes as OA;

#[OA\Put(
    path: '/api/products/{product}',
    summary: 'Update product',
    tags: ['Products'],
    security: [['bearerAuth' => []]],
    parameters: [
        new OA\Parameter(
            name: 'product',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'integer')
        ),
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: '#/components/schemas/Product')
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Product updated',
            content: new OA\JsonContent(ref: '#/components/schemas/Product')
        ),
    ]
)]
class UpdateProduct {}
