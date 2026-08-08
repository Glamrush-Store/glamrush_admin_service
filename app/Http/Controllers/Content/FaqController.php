<?php

namespace App\Http\Controllers\Content;

use App\Domain\Content\Services\FaqService;
use App\Http\Requests\Content\ListFaqsRequest;
use App\Http\Requests\Content\ReorderContentRequest;
use App\Http\Requests\Content\UpsertFaqRequest;
use App\Http\Resources\Content\FaqResource;
use App\Http\Responses\ApiResponse;
use App\Models\Faq;
use App\Models\FaqCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FaqController
{
    public function __construct(private FaqService $service) {}

    public function index(ListFaqsRequest $request): JsonResponse
    {
        $f = $request->validated();
        $q = Faq::query()->with(['category', 'storefronts', 'createdBy', 'updatedBy']);
        $q->when($f['search'] ?? null, fn (Builder $q, $v) => $q->where(fn (Builder $q) => $q->where('question', 'like', "%$v%")->orWhere('answer', 'like', "%$v%")))
            ->when($f['faq_category_id'] ?? null, fn (Builder $q, $v) => $q->where('faq_category_id', $v))->when(array_key_exists('is_published', $f), fn (Builder $q) => $q->where('is_published', $f['is_published']))
            ->when($f['storefront_id'] ?? null, fn (Builder $q, $v) => $q->where(fn (Builder $q) => $q->global()->orWhereHas('storefronts', fn (Builder $q) => $q->where('categories.id', $v))));
        $this->state($q, $f['state'] ?? null);
        if (isset($f['sort'])) {
            $q->orderBy($f['sort'], $f['direction'] ?? 'asc');
        } else {
            $q->orderBy(FaqCategory::select('display_order')->whereColumn('faq_categories.id', 'faqs.faq_category_id'))->orderBy('display_order')->orderBy('created_at')->orderBy('id');
        }

        return ApiResponse::success(FaqResource::collection($q->paginate($f['per_page'] ?? 20)->withQueryString()));
    }

    public function store(UpsertFaqRequest $request): JsonResponse
    {
        return ApiResponse::success(new FaqResource($this->service->create($request->validated(), $request->user())), 'FAQ created', 201);
    }

    public function show(Faq $faq): JsonResponse
    {
        return ApiResponse::success(new FaqResource($faq->load(['category', 'storefronts', 'createdBy', 'updatedBy'])));
    }

    public function update(UpsertFaqRequest $request, Faq $faq): JsonResponse
    {
        return ApiResponse::success(new FaqResource($this->service->update($faq, $request->validated(), $request->user())), 'FAQ updated');
    }

    public function publish(Request $request, Faq $faq): JsonResponse
    {
        return ApiResponse::success(new FaqResource($this->service->setPublished($faq, true, $request->user())), 'FAQ published');
    }

    public function unpublish(Request $request, Faq $faq): JsonResponse
    {
        return ApiResponse::success(new FaqResource($this->service->setPublished($faq, false, $request->user())), 'FAQ unpublished');
    }

    public function duplicate(Request $request, Faq $faq): JsonResponse
    {
        return ApiResponse::success(new FaqResource($this->service->duplicate($faq->load('storefronts'), $request->user())), 'FAQ duplicated', 201);
    }

    public function destroy(Request $request, Faq $faq): JsonResponse
    {
        $this->service->delete($faq, $request->user());

        return ApiResponse::success(null, 'FAQ deleted');
    }

    public function reorder(ReorderContentRequest $request): JsonResponse
    {
        $this->service->reorder($request->validated('ids'), $request->user());

        return ApiResponse::success(null, 'FAQs reordered');
    }

    private function state(Builder $q, ?string $state): void
    {
        if ($state) {
            match ($state) {
                'draft' => $q->draft(), 'scheduled' => $q->scheduled(), 'published' => $q->published(), 'unpublished' => $q->unpublished(), 'expired' => $q->expired()
            };
        }
    }
}
