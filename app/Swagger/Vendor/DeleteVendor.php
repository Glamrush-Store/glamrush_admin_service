<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Swagger\Vendor;

use OpenApi\Attributes as OA;

#[OA\Delete(
    path: 'api/v1/vendors/{vendor}',
    tags: ['Vendors'],
    summary: 'Delete vendor',
    security: [['bearerAuth' => []]],
    parameters: [
        new OA\Parameter(
            name: 'vendor',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'string')
        ),
    ],
    responses: [
        new OA\Response(response: 204, description: 'Vendor deleted'),
    ]
)]
class DeleteVendor {}
