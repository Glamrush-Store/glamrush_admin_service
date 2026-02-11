<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Swagger\Vendor\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PaginatedVendor',
    type: 'object',
    required: ['data', 'current_page', 'per_page', 'total', 'last_page'],
)]
class PaginatedVendor
{
    #[OA\Property(
        type: 'array',
        items: new OA\Items(ref: '#/components/schemas/Vendor')
    )]
    public array $data;

    #[OA\Property(example: 1)]
    public int $current_page;

    #[OA\Property(example: 15)]
    public int $per_page;

    #[OA\Property(example: 120)]
    public int $total;

    #[OA\Property(example: 8)]
    public int $last_page;
}
