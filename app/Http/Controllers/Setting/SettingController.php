<?php

namespace App\Http\Controllers\Setting;

use App\Http\Requests\Setting\ListSettingsRequest;
use App\Http\Requests\Setting\UpsertSettingRequest;
use App\Http\Resources\Setting\SettingResource;
use App\Http\Responses\ApiResponse;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class SettingController
{
    public function index(ListSettingsRequest $request): JsonResponse
    {
        $data = $request->validated();
        $settings = Setting::query()
            ->with('category')
            ->when($data['category_id'] ?? null, fn ($query, $categoryId) => $query->where('setting_category_id', $categoryId))
            ->when($data['category'] ?? null, fn ($query, $category) => $query->whereHas('category', fn ($q) => $q->where('slug', $category)))
            ->when($data['search'] ?? null, function ($query, $search): void {
                $query->where(fn ($q) => $q->where('key', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%"));
            })
            ->when(array_key_exists('is_public', $data), fn ($query) => $query->where('is_public', $data['is_public']))
            ->when(array_key_exists('is_active', $data), fn ($query) => $query->where('is_active', $data['is_active']))
            ->orderByRaw('(select sort_order from setting_categories where setting_categories.id = settings.setting_category_id)')
            ->orderBy('key')
            ->paginate($data['per_page'] ?? 50);

        return ApiResponse::success(SettingResource::collection($settings));
    }

    public function store(UpsertSettingRequest $request): JsonResponse
    {
        $setting = Setting::create($this->payload($request->validated()) + ['value_type' => 'string', 'is_public' => false, 'is_active' => true]);
        $this->clearCache();

        return ApiResponse::success(new SettingResource($setting->load('category')), 'Setting created', 201);
    }

    public function show(Setting $setting): JsonResponse
    {
        return ApiResponse::success(new SettingResource($setting->load('category')));
    }

    public function update(UpsertSettingRequest $request, Setting $setting): JsonResponse
    {
        $setting->update($this->payload($request->validated()));
        $this->clearCache();

        return ApiResponse::success(new SettingResource($setting->refresh()->load('category')), 'Setting updated');
    }

    public function destroy(Setting $setting): JsonResponse
    {
        $setting->delete();
        $this->clearCache();

        return ApiResponse::success(null, 'Setting deleted');
    }

    private function payload(array $data): array
    {
        if (array_key_exists('value', $data)) {
            $data['value'] = ['value' => $data['value']];
        }

        return $data;
    }

    private function clearCache(): void
    {
        Cache::forget('site-settings:public:all');
        Cache::forget('runtime-settings:configured:v1');
    }
}

