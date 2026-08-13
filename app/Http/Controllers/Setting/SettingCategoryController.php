<?php

namespace App\Http\Controllers\Setting;

use App\Http\Requests\Setting\UpsertSettingCategoryRequest;
use App\Http\Resources\Setting\SettingCategoryResource;
use App\Http\Responses\ApiResponse;
use App\Models\SettingCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingCategoryController
{
    public function index(Request $request): JsonResponse
    {
        $categories = SettingCategory::query()
            ->withCount('settings')
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search')->toString();
                $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%"));
            })
            ->when($request->has('is_active'), fn ($query) => $query->where('is_active', $request->boolean('is_active')))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($request->integer('per_page', 100));

        return ApiResponse::success(SettingCategoryResource::collection($categories));
    }

    public function store(UpsertSettingCategoryRequest $request): JsonResponse
    {
        $category = SettingCategory::create($request->validated() + ['sort_order' => 0, 'is_active' => true]);
        $this->clearCache();

        return ApiResponse::success(new SettingCategoryResource($category->loadCount('settings')), 'Setting category created', 201);
    }

    public function show(SettingCategory $settingCategory): JsonResponse
    {
        return ApiResponse::success(new SettingCategoryResource($settingCategory->load(['settings.category'])->loadCount('settings')));
    }

    public function update(UpsertSettingCategoryRequest $request, SettingCategory $settingCategory): JsonResponse
    {
        $settingCategory->update($request->validated());
        $this->clearCache();

        return ApiResponse::success(new SettingCategoryResource($settingCategory->refresh()->loadCount('settings')), 'Setting category updated');
    }

    public function destroy(SettingCategory $settingCategory): JsonResponse
    {
        $settingCategory->delete();
        $this->clearCache();

        return ApiResponse::success(null, 'Setting category deleted');
    }

    private function clearCache(): void
    {
        Cache::forget('site-settings:public:all');
        Cache::forget('runtime-settings:configured:v1');
    }
}
