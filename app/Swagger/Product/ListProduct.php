<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Swagger\Product;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/products',
    summary: 'List products',
    security: [['bearerAuth' => []]],
    tags: ['Products'],
    parameters: [
        new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer')),
        new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer')),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Paginated product list',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'data',
                        type: 'array',
                        items: new OA\Items(ref: '#/components/schemas/Product')
                    ),
                    new OA\Property(property: 'current_page', type: 'integer'),
                    new OA\Property(property: 'last_page', type: 'integer'),
                    new OA\Property(property: 'total', type: 'integer'),
                ]
            )
        ),
    ]
)]
class ListProduct {}
