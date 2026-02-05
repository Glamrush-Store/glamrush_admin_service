<?php
/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */


namespace App\Swagger\Category\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Category',
    type: 'object',
    required: ['id', 'name', 'slug', 'is_active'],
)]
class Category
{
    #[OA\Property(example: '01KGNEFG4AR01DM9BY7PS0P5JD')]
    public string $id;

    #[OA\Property(example: 'Electronics')]
    public string $name;

    #[OA\Property(example: 'electronics')]
    public string $slug;

    #[OA\Property(nullable: true, example: null)]
    public ?string $parent_id;

    #[OA\Property(example: true)]
    public bool $is_active;

    #[OA\Property(nullable: true)]
    public ?string $description;

    #[OA\Property(nullable: true)]
    public ?string $meta_title;

    #[OA\Property(nullable: true)]
    public ?string $meta_description;

    #[OA\Property(nullable: true)]
    public ?string $meta_keywords;

    #[OA\Property(example: 1)]
    public int $sort_order;

    #[OA\Property(format: 'date-time')]
    public string $created_at;

    #[OA\Property(format: 'date-time')]
    public string $updated_at;
}
