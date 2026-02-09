<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Swagger\Brand;

use OpenApi\Attributes as OA;

#[OA\Delete(
    path: '/api/brands/{brand}',
    summary: 'Delete a brand',
    tags: ['Brands'],
    security: [['bearerAuth' => []]],
    parameters: [
        new OA\PathParameter(
            name: 'brand',
            required: true,
            schema: new OA\Schema(type: 'string'),
            example: 'apple'
        ),
    ],
    responses: [
        new OA\Response(response: 204, description: 'Brand deleted'),
        new OA\Response(response: 404, description: 'Brand not found'),
    ]
)]
class DeleteBrand {}
