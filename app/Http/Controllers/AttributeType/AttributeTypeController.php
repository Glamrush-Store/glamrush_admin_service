<?php

namespace App\Http\Controllers\AttributeType;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttributeType\ListAttributeTypesRequest;
use App\Http\Requests\AttributeType\UpsertAttributeTypeRequest;
use App\Http\Resources\AttributeType\AttributeTypeResource;
use App\Http\Responses\ApiResponse;
use App\Models\AttributeType;
use App\Models\SkuAttributeCode;
use Illuminate\Http\JsonResponse;

class AttributeTypeController extends Controller
{
    public function index(ListAttributeTypesRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $sortBy = $filters['sort_by'] ?? 'value';
        $sortDir = $filters['sort_dir'] ?? 'asc';

        $query = AttributeType::query()
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(function ($query) use ($search) {
                $query->where('value', 'like', "%{$search}%")
                    ->orWhere('label', 'like', "%{$search}%");
            }))
            ->when($filters['category'] ?? null, fn ($query, $category) => $query->where('category', $category))
            ->orderBy($sortBy, $sortDir)
            ->orderBy('id');

        return ApiResponse::success(AttributeTypeResource::collection($query->paginate($filters['per_page'] ?? 15)));
    }

    public function store(UpsertAttributeTypeRequest $request): JsonResponse
    {
        $attributeType = AttributeType::create($request->validated());

        return ApiResponse::success(new AttributeTypeResource($attributeType), 'Attribute type created', 201);
    }

    public function show(AttributeType $attributeType): JsonResponse
    {
        return ApiResponse::success(new AttributeTypeResource($attributeType));
    }

    public function update(UpsertAttributeTypeRequest $request, AttributeType $attributeType): JsonResponse
    {
        $data = $request->validated();

        if (isset($data['value']) && $data['value'] !== $attributeType->value && $this->isInUse($attributeType)) {
            return ApiResponse::error('Attribute type is in use by SKU attribute codes', [], 409);
        }

        $attributeType->update($data);

        return ApiResponse::success(new AttributeTypeResource($attributeType), 'Attribute type updated');
    }

    public function destroy(AttributeType $attributeType): JsonResponse
    {
        if ($this->isInUse($attributeType)) {
            return ApiResponse::error('Attribute type is in use by SKU attribute codes', [], 409);
        }

        $attributeType->delete();

        return ApiResponse::success(null, 'Attribute type deleted');
    }

    private function isInUse(AttributeType $attributeType): bool
    {
        return SkuAttributeCode::query()->where('type', $attributeType->value)->exists();
    }
}
