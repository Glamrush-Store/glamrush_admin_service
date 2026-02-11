<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Swagger\Category;

use OpenApi\Attributes as OA;

#[OA\Delete(
    path: '/api/categories/{category}',
    summary: 'Delete a category',
    tags: ['Categories'],
    security: [['bearerAuth' => []]],
    parameters: [
        new OA\PathParameter(
            name: 'category',
            required: true,
            schema: new OA\Schema(type: 'string')
        ),
    ],
    responses: [
        new OA\Response(response: 204, description: 'Category deleted'),
        new OA\Response(response: 404, description: 'Category not found'),
    ]
)]
class DeleteCategory {}
