<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Requests\Storefront\ReorderStorefrontSectionsRequest;
use App\Http\Requests\Storefront\StorefrontHomepageSectionRequest;
use App\Models\StorefrontHomepageSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DefaultStorefrontHomepageSectionController
{
    public function __construct(private readonly StorefrontHomepageSectionController $sections) {}

    public function index(): JsonResponse
    {
        return $this->sections->index($this->storefront());
    }

    public function store(StorefrontHomepageSectionRequest $request): JsonResponse
    {
        return $this->sections->store($request, $this->storefront());
    }

    public function show(StorefrontHomepageSection $section): JsonResponse
    {
        return $this->sections->show($this->storefront(), $section);
    }

    public function update(StorefrontHomepageSectionRequest $request, StorefrontHomepageSection $section): JsonResponse
    {
        return $this->sections->update($request, $this->storefront(), $section);
    }

    public function destroy(StorefrontHomepageSection $section): JsonResponse
    {
        return $this->sections->destroy($this->storefront(), $section);
    }

    public function enable(Request $request, StorefrontHomepageSection $section): JsonResponse
    {
        return $this->sections->enable($request, $this->storefront(), $section);
    }

    public function disable(Request $request, StorefrontHomepageSection $section): JsonResponse
    {
        return $this->sections->disable($request, $this->storefront(), $section);
    }

    public function reorder(ReorderStorefrontSectionsRequest $request): JsonResponse
    {
        return $this->sections->reorder($request, $this->storefront());
    }

    private function storefront(): string
    {
        return (string) config('services.storefront.default_slug', 'fragrances');
    }
}
