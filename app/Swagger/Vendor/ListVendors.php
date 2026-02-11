<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Swagger\Vendor;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/vendors',
    summary: 'List vendors',
    security: [['bearerAuth' => []]],
    tags: ['Vendors'],
    parameters: [
        new OA\Parameter(
            name: 'page',
            in: 'query',
            schema: new OA\Schema(type: 'integer')
        ),
        new OA\Parameter(
            name: 'per_page',
            in: 'query',
            schema: new OA\Schema(type: 'integer')
        ),
        new OA\Parameter(
            name: 'search',
            in: 'query',
            schema: new OA\Schema(type: 'string')
        ),
        new OA\Parameter(
            name: 'country',
            in: 'query',
            schema: new OA\Schema(type: 'string')
        ),
        new OA\Parameter(
            name: 'is_active',
            in: 'query',
            schema: new OA\Schema(type: 'boolean')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Paginated vendor list',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'data',
                        type: 'array',
                        items: new OA\Items(ref: '#/components/schemas/Vendor')
                    ),
                    new OA\Property(
                        property: 'current_page',
                        type: 'integer'
                    ),
                    new OA\Property(
                        property: 'last_page',
                        type: 'integer'
                    ),
                    new OA\Property(
                        property: 'total',
                        type: 'integer'
                    ),
                ]
            )
        ),
    ]
)]
class ListVendors {}
