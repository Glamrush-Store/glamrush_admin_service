<?php

namespace App\Http\Controllers\Storefront;

use App\Domain\Storefront\Enums\HomepageSectionType;
use App\Http\Requests\Storefront\ReorderStorefrontSectionsRequest;
use App\Http\Requests\Storefront\StorefrontHomepageSectionRequest;
use App\Http\Resources\Storefront\StorefrontHomepageSectionResource;
use App\Http\Responses\ApiResponse;
use App\Infrastructure\Cache\StorefrontHomepageCache;
use App\Models\Category;
use App\Models\StorefrontHomepageSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class StorefrontHomepageSectionController
{
    public function types(): JsonResponse
    {
        return ApiResponse::success(collect(HomepageSectionType::cases())->map(fn (HomepageSectionType $type) => [
            'label' => str($type->value)->replace('_', ' ')->title()->toString(),
            'value' => $type->value,
            'config_keys' => $type->allowedConfigKeys(),
        ])->values());
    }

    public function index(string $storefront): JsonResponse
    {
        $this->ensureStorefront($storefront);
        $sections = StorefrontHomepageSection::where('storefront_slug', $storefront)->with('products:id')->orderBy('display_order')->paginate(50);

        return ApiResponse::success(StorefrontHomepageSectionResource::collection($sections));
    }

    public function store(StorefrontHomepageSectionRequest $request, string $storefront): JsonResponse
    {
        $this->ensureStorefront($storefront);
        $section = DB::transaction(function () use ($request, $storefront) {
            $data = $request->validated();
            $productIds = Arr::pull($data, 'config.product_ids', []);
            $section = StorefrontHomepageSection::create([...$data, 'storefront_slug' => $storefront, 'created_by' => $request->user()->id, 'updated_by' => $request->user()->id]);
            $this->syncProducts($section, $productIds);

            return $section;
        });

        return ApiResponse::success(new StorefrontHomepageSectionResource($section->refresh()->load('products:id')), 'Section created', 201);
    }

    public function show(string $storefront, StorefrontHomepageSection $section): JsonResponse
    {
        $this->ensureOwned($storefront, $section);

        return ApiResponse::success(new StorefrontHomepageSectionResource($section->load('products:id')));
    }

    public function update(StorefrontHomepageSectionRequest $request, string $storefront, StorefrontHomepageSection $section): JsonResponse
    {
        $this->ensureOwned($storefront, $section);
        DB::transaction(function () use ($request, $section) {
            $data = $request->validated();
            $hasProducts = Arr::has($data, 'config.product_ids');
            $productIds = Arr::pull($data, 'config.product_ids', []);
            $section->update([...$data, 'updated_by' => $request->user()->id]);
            if ($hasProducts || $section->type !== HomepageSectionType::ManualProducts) {
                $this->syncProducts($section, $productIds);
                $section->touch();
            }
        });

        return ApiResponse::success(new StorefrontHomepageSectionResource($section->refresh()->load('products:id')), 'Section updated');
    }

    public function destroy(string $storefront, StorefrontHomepageSection $section): JsonResponse
    {
        $this->ensureOwned($storefront, $section);
        $section->delete();

        return ApiResponse::success(null, 'Section deleted');
    }

    public function enable(Request $request, string $storefront, StorefrontHomepageSection $section): JsonResponse
    {
        return $this->setActive($request, $storefront, $section, true);
    }

    public function disable(Request $request, string $storefront, StorefrontHomepageSection $section): JsonResponse
    {
        return $this->setActive($request, $storefront, $section, false);
    }

    public function reorder(ReorderStorefrontSectionsRequest $request, string $storefront): JsonResponse
    {
        $this->ensureStorefront($storefront);
        $ids = $request->validated('section_ids');
        abort_unless(StorefrontHomepageSection::where('storefront_slug', $storefront)->whereIn('id', $ids)->count() === count($ids), 422, 'Every section must belong to the storefront.');

        DB::transaction(function () use ($ids, $storefront) {
            foreach ($ids as $order => $id) {
                StorefrontHomepageSection::whereKey($id)->update(['display_order' => $order + 1]);
            }
            StorefrontHomepageCache::forget($storefront);
        });

        $sections = StorefrontHomepageSection::whereIn('id', $ids)->with('products:id')->orderBy('display_order')->get();

        return ApiResponse::success(StorefrontHomepageSectionResource::collection($sections), 'Sections reordered');
    }

    private function setActive(Request $request, string $storefront, StorefrontHomepageSection $section, bool $active): JsonResponse
    {
        $this->ensureOwned($storefront, $section);
        $section->update(['is_active' => $active, 'updated_by' => $request->user()->id]);

        return ApiResponse::success(new StorefrontHomepageSectionResource($section->load('products:id')), $active ? 'Section enabled' : 'Section disabled');
    }

    private function syncProducts(StorefrontHomepageSection $section, array $productIds): void
    {
        $section->products()->sync(collect($productIds)->mapWithKeys(fn (string $id, int $order) => [$id => ['display_order' => $order + 1]])->all());
    }

    private function ensureStorefront(string $storefront): void
    {
        abort_unless(Category::where('slug', $storefront)->whereNull('parent_id')->where('is_active', true)->exists(), 404, 'Storefront not found.');
    }

    private function ensureOwned(string $storefront, StorefrontHomepageSection $section): void
    {
        abort_unless($section->storefront_slug === $storefront, 404);
    }
}
