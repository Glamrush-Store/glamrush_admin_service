<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Swagger\Vendor;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/vendors/{vendor}',
    tags: ['Vendors'],
    summary: 'Show vendor',
    security: [['bearerAuth' => []]],
    parameters: [
        new OA\Parameter(
            name: 'vendor',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'string'),
            description: 'Vendor ULID'
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Vendor details',
            content: new OA\JsonContent(ref: '#/components/schemas/Vendor')
        ),
        new OA\Response(response: 404, description: 'Vendor not found'),
    ]
)]

class ShowVendor {}
