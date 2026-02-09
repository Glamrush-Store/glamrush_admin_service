<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Swagger\Product;

use OpenApi\Attributes as OA;

#[OA\Delete(
    path: '/api/products/{product}',
    summary: 'Delete product',
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
        new OA\Response(response: 204, description: 'Product deleted'),
    ]
)]
class DeleteProduct {}
