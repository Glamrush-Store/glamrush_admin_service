<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Requests\Storefront\StorefrontCampaignRequest;
use App\Http\Resources\Storefront\StorefrontCampaignResource;
use App\Http\Responses\ApiResponse;
use App\Models\Category;
use App\Models\StorefrontCampaign;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class StorefrontCampaignController
{
    public function index(string $storefront): JsonResponse
    {
        $this->ensureStorefront($storefront);
        $campaigns = StorefrontCampaign::where('storefront_slug', $storefront)->orderByDesc('priority')->orderByDesc('created_at')->paginate(20);

        return ApiResponse::success(StorefrontCampaignResource::collection($campaigns));
    }

    public function store(StorefrontCampaignRequest $request, string $storefront): JsonResponse
    {
        $this->ensureStorefront($storefront);
        $campaign = DB::transaction(function () use ($request, $storefront) {
            $campaign = StorefrontCampaign::create([
                ...Arr::except($request->validated(), ['desktop_image', 'mobile_image']),
                'storefront_slug' => $storefront,
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
            ]);
            $this->attachImages($campaign, $request);

            return $campaign;
        });

        return ApiResponse::success(new StorefrontCampaignResource($campaign->refresh()), 'Campaign created', 201);
    }

    public function show(string $storefront, StorefrontCampaign $campaign): JsonResponse
    {
        $this->ensureOwned($storefront, $campaign);

        return ApiResponse::success(new StorefrontCampaignResource($campaign));
    }

    public function update(StorefrontCampaignRequest $request, string $storefront, StorefrontCampaign $campaign): JsonResponse
    {
        $this->ensureOwned($storefront, $campaign);
        DB::transaction(function () use ($request, $campaign) {
            $campaign->update([...Arr::except($request->validated(), ['desktop_image', 'mobile_image']), 'updated_by' => $request->user()->id]);
            $this->attachImages($campaign, $request);
        });

        return ApiResponse::success(new StorefrontCampaignResource($campaign->refresh()), 'Campaign updated');
    }

    public function destroy(string $storefront, StorefrontCampaign $campaign): JsonResponse
    {
        $this->ensureOwned($storefront, $campaign);
        $campaign->delete();

        return ApiResponse::success(null, 'Campaign deleted');
    }

    public function enable(Request $request, string $storefront, StorefrontCampaign $campaign): JsonResponse
    {
        return $this->setActive($request, $storefront, $campaign, true);
    }

    public function disable(Request $request, string $storefront, StorefrontCampaign $campaign): JsonResponse
    {
        return $this->setActive($request, $storefront, $campaign, false);
    }

    private function setActive(Request $request, string $storefront, StorefrontCampaign $campaign, bool $active): JsonResponse
    {
        $this->ensureOwned($storefront, $campaign);
        $campaign->update(['is_active' => $active, 'updated_by' => $request->user()->id]);

        return ApiResponse::success(new StorefrontCampaignResource($campaign), $active ? 'Campaign enabled' : 'Campaign disabled');
    }

    private function attachImages(StorefrontCampaign $campaign, StorefrontCampaignRequest $request): void
    {
        foreach (['desktop_image' => 'desktop-image', 'mobile_image' => 'mobile-image'] as $field => $collection) {
            if ($request->hasFile($field)) {
                $campaign->addMediaFromRequest($field)->toMediaCollection($collection);
            }
        }
    }

    private function ensureStorefront(string $storefront): void
    {
        abort_unless(Category::where('slug', $storefront)->whereNull('parent_id')->where('is_active', true)->exists(), 404, 'Storefront not found.');
    }

    private function ensureOwned(string $storefront, StorefrontCampaign $campaign): void
    {
        abort_unless($campaign->storefront_slug === $storefront, 404);
    }
}
