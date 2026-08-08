<?php

namespace App\Http\Controllers\Content;

use App\Domain\Content\Services\ContentPageService;
use App\Http\Requests\Content\DuplicateContentPageRequest;
use App\Http\Requests\Content\ListContentPagesRequest;
use App\Http\Requests\Content\UpsertContentPageRequest;
use App\Http\Resources\Content\ContentPageResource;
use App\Http\Responses\ApiResponse;
use App\Models\ContentPage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContentPageController
{
    public function __construct(private ContentPageService $service) {}

    public function index(ListContentPagesRequest $request): JsonResponse
    {
        $f = $request->validated();
        $q = ContentPage::query()->with(['storefronts', 'createdBy', 'updatedBy', 'media']);
        $q->when($f['search'] ?? null, fn (Builder $q, $v) => $q->where(fn (Builder $q) => $q->where('title', 'like', "%$v%")->orWhere('navigation_title', 'like', "%$v%")->orWhere('slug', 'like', "%$v%")->orWhere('excerpt', 'like', "%$v%")->orWhere('content', 'like', "%$v%")))
            ->when($f['page_type'] ?? null, fn (Builder $q, $v) => $q->where('page_type', $v))->when(array_key_exists('is_published', $f), fn (Builder $q) => $q->where('is_published', $f['is_published']))
            ->when($f['storefront_id'] ?? null, fn (Builder $q, $v) => $q->where(fn (Builder $q) => $q->global()->orWhereHas('storefronts', fn (Builder $q) => $q->where('categories.id', $v))))
            ->when($f['published_from'] ?? null, fn (Builder $q, $v) => $q->where('published_at', '>=', $v))->when($f['published_to'] ?? null, fn (Builder $q, $v) => $q->where('published_at', '<=', $v))
            ->when($f['expires_from'] ?? null, fn (Builder $q, $v) => $q->where('expires_at', '>=', $v))->when($f['expires_to'] ?? null, fn (Builder $q, $v) => $q->where('expires_at', '<=', $v));
        $this->state($q, $f['state'] ?? null);
        $q->orderBy($f['sort'] ?? 'display_order', $f['direction'] ?? 'asc')->orderBy('id');

        return ApiResponse::success(ContentPageResource::collection($q->paginate($f['per_page'] ?? 20)->withQueryString()));
    }

    public function store(UpsertContentPageRequest $request): JsonResponse
    {
        return ApiResponse::success(new ContentPageResource($this->service->create($request->validated(), $request->user())), 'Content page created', 201);
    }

    public function show(ContentPage $contentPage): JsonResponse
    {
        return ApiResponse::success(new ContentPageResource($contentPage->load(['storefronts', 'createdBy', 'updatedBy', 'media'])));
    }

    public function update(UpsertContentPageRequest $request, ContentPage $contentPage): JsonResponse
    {
        return ApiResponse::success(new ContentPageResource($this->service->update($contentPage, $request->validated(), $request->user())), 'Content page updated');
    }

    public function publish(Request $request, ContentPage $contentPage): JsonResponse
    {
        return ApiResponse::success(new ContentPageResource($this->service->setPublished($contentPage, true, $request->user())), 'Content page published');
    }

    public function unpublish(Request $request, ContentPage $contentPage): JsonResponse
    {
        return ApiResponse::success(new ContentPageResource($this->service->setPublished($contentPage, false, $request->user())), 'Content page unpublished');
    }

    public function duplicate(DuplicateContentPageRequest $request, ContentPage $contentPage): JsonResponse
    {
        return ApiResponse::success(new ContentPageResource($this->service->duplicate($contentPage->load('storefronts'), $request->validated('slug'), $request->user())), 'Content page duplicated', 201);
    }

    public function destroy(Request $request, ContentPage $contentPage): JsonResponse
    {
        $this->service->delete($contentPage, $request->user());

        return ApiResponse::success(null, 'Content page deleted');
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
