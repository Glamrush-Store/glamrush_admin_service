<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Swagger\Vendor\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Vendor',
    type: 'object',
    required: ['id', 'name', 'email', 'code', 'is_active'],
)]
class Vendor
{
    #[OA\Property(example: '01HV9Q8RZJ6E7Y3P6KX8D9N1A2')]
    public string $id;

    #[OA\Property(example: 'John Doe')]
    public string $name;

    #[OA\Property(example: 'Glamrush Store')]
    public ?string $business_name;

    #[OA\Property(example: 'vendor@glamrush.com')]
    public string $email;

    #[OA\Property(example: 'GLAM1234')]
    public string $code;

    #[OA\Property(example: true)]
    public bool $is_active;

    #[OA\Property(example: 'Lagos')]
    public ?string $city;

    #[OA\Property(example: 'NG')]
    public ?string $country;

    #[OA\Property(format: 'date-time')]
    public string $created_at;
}
