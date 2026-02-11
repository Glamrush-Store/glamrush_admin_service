<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Swagger\Vendor;

use OpenApi\Attributes as OA;

#[OA\Put(
    path: '/vendors/{vendor}',
    tags: ['Vendors'],
    summary: 'Update vendor',
    security: [['bearerAuth' => []]],
    parameters: [
        new OA\Parameter(
            name: 'vendor',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'string')
        ),
    ],
    requestBody: new OA\RequestBody(
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'business_name', type: 'string'),
                new OA\Property(property: 'email', type: 'string'),
                new OA\Property(property: 'is_active', type: 'boolean'),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Vendor updated',
            content: new OA\JsonContent(ref: '#/components/schemas/Vendor')
        ),
    ]
)]

class UpdateVendor {}
