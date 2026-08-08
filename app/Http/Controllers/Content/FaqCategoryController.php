<?php

namespace App\Http\Controllers\Content;

use App\Domain\Content\Services\FaqCategoryService;
use App\Http\Requests\Content\ListFaqCategoriesRequest;
use App\Http\Requests\Content\ReorderContentRequest;
use App\Http\Requests\Content\UpsertFaqCategoryRequest;
use App\Http\Resources\Content\FaqCategoryResource;
use App\Http\Responses\ApiResponse;
use App\Models\FaqCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FaqCategoryController
{
    public function __construct(private FaqCategoryService $service) {}

    public function index(ListFaqCategoriesRequest $request): JsonResponse
    {
        $data = $request->validated();
        $q = FaqCategory::query()->withCount('faqs')->when($data['search'] ?? null, fn ($q, $v) => $q->where('name', 'like', "%$v%"))->when(array_key_exists('is_active', $data), fn ($q) => $q->where('is_active', $data['is_active']))->orderBy('display_order')->orderBy('id');

        return ApiResponse::success(FaqCategoryResource::collection($q->paginate($data['per_page'] ?? 100)));
    }

    public function store(UpsertFaqCategoryRequest $request): JsonResponse
    {
        return ApiResponse::success(new FaqCategoryResource($this->service->create($request->validated(), $request->user())), 'FAQ category created', 201);
    }

    public function show(FaqCategory $faqCategory): JsonResponse
    {
        return ApiResponse::success(new FaqCategoryResource($faqCategory->loadCount('faqs')));
    }

    public function update(UpsertFaqCategoryRequest $request, FaqCategory $faqCategory): JsonResponse
    {
        return ApiResponse::success(new FaqCategoryResource($this->service->update($faqCategory, $request->validated(), $request->user())), 'FAQ category updated');
    }

    public function destroy(Request $request, FaqCategory $faqCategory): JsonResponse
    {
        $this->service->delete($faqCategory, $request->user());

        return ApiResponse::success(null, 'FAQ category deleted');
    }

    public function reorder(ReorderContentRequest $request): JsonResponse
    {
        $this->service->reorder($request->validated('ids'), $request->user());

        return ApiResponse::success(null, 'FAQ categories reordered');
    }
}
