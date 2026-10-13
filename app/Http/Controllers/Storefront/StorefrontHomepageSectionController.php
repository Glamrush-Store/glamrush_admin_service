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
            'media_fields' => $type === HomepageSectionType::SplitImageBanner
                ? ['left_image', 'right_image', 'mobile_image']
                : [],
        ])->values());
    }

    public function index(string $storefront): JsonResponse
    {
        $this->ensureStorefront($storefront);
        $sections = StorefrontHomepageSection::where('storefront_slug', $storefront)
            ->with(['products:id', 'media'])
            ->orderBy('display_order')
            ->orderBy('id')
            ->paginate(50);

        return ApiResponse::success(StorefrontHomepageSectionResource::collection($sections));
    }

    public function store(StorefrontHomepageSectionRequest $request, string $storefront): JsonResponse
    {
        $this->ensureStorefront($storefront);
        $section = DB::transaction(function () use ($request, $storefront) {
            $data = $this->normalizedData($request->validated());
            $productIds = Arr::pull($data, 'config.product_ids', []);
            $section = StorefrontHomepageSection::create([
                ...Arr::except($data, ['left_image', 'right_image', 'mobile_image']),
                'storefront_slug' => $storefront,
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
            ]);
            $this->syncProducts($section, $productIds);
            $this->attachImages($section, $request);

            return $section;
        });

        return ApiResponse::success(new StorefrontHomepageSectionResource($section->refresh()->load(['products:id', 'media'])), 'Section created', 201);
    }

    public function show(string $storefront, StorefrontHomepageSection $section): JsonResponse
    {
        $this->ensureOwned($storefront, $section);

        return ApiResponse::success(new StorefrontHomepageSectionResource($section->load(['products:id', 'media'])));
    }

    public function update(StorefrontHomepageSectionRequest $request, string $storefront, StorefrontHomepageSection $section): JsonResponse
    {
        $this->ensureOwned($storefront, $section);
        DB::transaction(function () use ($request, $section) {
            $data = $this->normalizedData($request->validated(), $section);
            $hasProducts = Arr::has($data, 'config.product_ids');
            $productIds = Arr::pull($data, 'config.product_ids', []);
            $section->update([
                ...Arr::except($data, ['left_image', 'right_image', 'mobile_image']),
                'updated_by' => $request->user()->id,
            ]);
            $this->attachImages($section, $request);
            if ($hasProducts || $section->type !== HomepageSectionType::ManualProducts) {
                $this->syncProducts($section, $productIds);
                $section->touch();
            }
        });

        return ApiResponse::success(new StorefrontHomepageSectionResource($section->refresh()->load(['products:id', 'media'])), 'Section updated');
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

        $sections = StorefrontHomepageSection::whereIn('id', $ids)->with(['products:id', 'media'])->orderBy('display_order')->orderBy('id')->get();

        return ApiResponse::success(StorefrontHomepageSectionResource::collection($sections), 'Sections reordered');
    }

    private function setActive(Request $request, string $storefront, StorefrontHomepageSection $section, bool $active): JsonResponse
    {
        $this->ensureOwned($storefront, $section);
        $section->update(['is_active' => $active, 'updated_by' => $request->user()->id]);

        return ApiResponse::success(new StorefrontHomepageSectionResource($section->load(['products:id', 'media'])), $active ? 'Section enabled' : 'Section disabled');
    }

    private function syncProducts(StorefrontHomepageSection $section, array $productIds): void
    {
        $section->products()->sync(collect($productIds)->mapWithKeys(fn (string $id, int $order) => [$id => ['display_order' => $order + 1]])->all());
    }

    private function attachImages(StorefrontHomepageSection $section, StorefrontHomepageSectionRequest $request): void
    {
        $attached = false;
        foreach ([
            'left_image' => 'left-image',
            'right_image' => 'right-image',
            'mobile_image' => 'mobile-image',
        ] as $field => $collection) {
            if ($request->hasFile($field)) {
                $section->addMediaFromRequest($field)->toMediaCollection($collection);
                $attached = true;
            }
        }

        if ($attached) {
            StorefrontHomepageCache::forget($section->storefront_slug);
        }
    }

    private function normalizedData(array $data, ?StorefrontHomepageSection $section = null): array
    {
        $type = $data['type'] ?? $section?->type;
        $typeValue = $type instanceof HomepageSectionType ? $type->value : $type;

        if ($typeValue === HomepageSectionType::SplitImageBanner->value && isset($data['config']['items'])) {
            $data['config']['items'] = collect($data['config']['items'])
                ->sortBy('sort_order')
                ->values()
                ->all();
        }

        return $data;
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
