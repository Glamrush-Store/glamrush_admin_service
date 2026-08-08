<?php

namespace App\Http\Controllers\Discount;

use App\Domain\Discount\Services\DiscountCodeService;
use App\Http\Requests\Discount\DuplicateDiscountCodeRequest;
use App\Http\Requests\Discount\ListDiscountCodesRequest;
use App\Http\Requests\Discount\UpsertDiscountCodeRequest;
use App\Http\Resources\Discount\DiscountCodeResource;
use App\Http\Responses\ApiResponse;
use App\Models\DiscountCode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiscountCodeController
{
    public function __construct(private DiscountCodeService $service) {}

    public function index(ListDiscountCodesRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $query = DiscountCode::query()->with(['storefronts', 'targets', 'createdBy', 'updatedBy']);
        $query->when($filters['search'] ?? null, fn (Builder $q, $search) => $q->where(fn (Builder $q) => $q->where('code', 'like', "%$search%")->orWhere('name', 'like', "%$search%")->orWhere('description', 'like', "%$search%")))
            ->when($filters['type'] ?? null, fn (Builder $q, $type) => $q->where('type', $type))
            ->when(array_key_exists('is_active', $filters), fn (Builder $q) => $q->where('is_active', $filters['is_active']))
            ->when($filters['storefront_id'] ?? null, fn (Builder $q, $id) => $q->where(fn (Builder $q) => $q->where('applies_to_all_storefronts', true)->orWhereHas('storefronts', fn (Builder $q) => $q->where('categories.id', $id))))
            ->when($filters['starts_at_from'] ?? null, fn (Builder $q, $v) => $q->where('starts_at', '>=', $v))
            ->when($filters['starts_at_to'] ?? null, fn (Builder $q, $v) => $q->where('starts_at', '<=', $v))
            ->when($filters['ends_at_from'] ?? null, fn (Builder $q, $v) => $q->where('ends_at', '>=', $v))
            ->when($filters['ends_at_to'] ?? null, fn (Builder $q, $v) => $q->where('ends_at', '<=', $v));
        $this->applyState($query, $filters['state'] ?? null);
        $query->orderBy($filters['sort'] ?? 'created_at', $filters['direction'] ?? 'desc');

        return ApiResponse::success(DiscountCodeResource::collection($query->paginate($filters['per_page'] ?? 20)->withQueryString()));
    }

    public function store(UpsertDiscountCodeRequest $request): JsonResponse
    {
        return ApiResponse::success(new DiscountCodeResource($this->service->create($request->validated(), $request->user())), 'Discount code created', 201);
    }

    public function show(DiscountCode $discountCode): JsonResponse
    {
        return ApiResponse::success(new DiscountCodeResource($discountCode->load(['storefronts', 'targets', 'createdBy', 'updatedBy'])));
    }

    public function update(UpsertDiscountCodeRequest $request, DiscountCode $discountCode): JsonResponse
    {
        return ApiResponse::success(new DiscountCodeResource($this->service->update($discountCode, $request->validated(), $request->user())), 'Discount code updated');
    }

    public function activate(Request $request, DiscountCode $discountCode): JsonResponse
    {
        return ApiResponse::success(new DiscountCodeResource($this->service->setActive($discountCode, true, $request->user())), 'Discount code activated');
    }

    public function deactivate(Request $request, DiscountCode $discountCode): JsonResponse
    {
        return ApiResponse::success(new DiscountCodeResource($this->service->setActive($discountCode, false, $request->user())), 'Discount code deactivated');
    }

    public function duplicate(DuplicateDiscountCodeRequest $request, DiscountCode $discountCode): JsonResponse
    {
        return ApiResponse::success(new DiscountCodeResource($this->service->duplicate($discountCode->load(['storefronts', 'targets']), $request->validated('code'), $request->user())), 'Discount code duplicated', 201);
    }

    private function applyState(Builder $query, ?string $state): void
    {
        if (! $state) {
            return;
        }
        match ($state) {
            'expired' => $query->expired(),
            'scheduled' => $query->scheduled(),
            'active' => $query->active(),
            'draft' => $query->where('is_active', false)->whereNull('starts_at')->whereNull('ends_at'),
            'inactive' => $query->where('is_active', false)->where(fn (Builder $q) => $q->whereNotNull('starts_at')->orWhereNotNull('ends_at'))->where(fn (Builder $q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now())),
        };
    }
}
