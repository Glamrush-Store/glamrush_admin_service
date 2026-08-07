<?php

namespace App\Http\Controllers\Storefront;

use App\Domain\Storefront\Services\PublishedStorefrontHomepage;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class PublishedStorefrontHomepageController
{
    public function __invoke(string $storefront, PublishedStorefrontHomepage $homepage): JsonResponse
    {
        abort_unless(Category::where('slug', $storefront)->whereNull('parent_id')->where('is_active', true)->exists(), 404, 'Storefront not found.');

        return response()->json(['data' => $homepage->get($storefront)]);
    }
}
