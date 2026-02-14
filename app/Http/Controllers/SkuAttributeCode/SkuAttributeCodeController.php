<?php
/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Controllers\SkuAttributeCode;

use App\Http\Responses\ApiResponse;
use App\Models\SkuAttributeCode;
use App\Http\Controllers\Controller;
use App\Http\Resources\SkuAttributeCode\SkuAttributeCodeResource;
use App\Http\Requests\SkuAttributeCode\CreateSkuAttributeCodeRequest;
use App\Http\Requests\SkuAttributeCode\UpdateSkuAttributeCodeRequest;
use App\Support\AttributeType;
class SkuAttributeCodeController extends Controller
{
    public function index()
    {
        $items = SkuAttributeCode::query()
            ->when(request('search'), fn ($q, $v) =>
            $q->where('value', 'like', "%{$v}%")
                ->orWhere('code', 'like', "%{$v}%")
            )->when(request('type'), fn ($q, $v) =>
            $q->where('type', $v)
            ->paginate(15));

        return ApiResponse::success(SkuAttributeCodeResource::collection($items));
    }




    public function types()
    {
           return ApiResponse::success(AttributeType::formatted());
    }


    public function store(CreateSkuAttributeCodeRequest $request)
    {
        $item = SkuAttributeCode::create($request->validated());

        return ApiResponse::success(new SkuAttributeCodeResource($item),'OK', 201);
    }

    public function show(SkuAttributeCode $skuAttributeCode)
    {
        return ApiResponse::success(new SkuAttributeCodeResource($skuAttributeCode));
    }

    public function update(UpdateSkuAttributeCodeRequest $request, SkuAttributeCode $skuAttributeCode)
    {
        $skuAttributeCode->update($request->validated());

        return ApiResponse::success(new SkuAttributeCodeResource($skuAttributeCode));
    }

    public function destroy(SkuAttributeCode $skuAttributeCode)
    {
        $skuAttributeCode->delete();

        return ApiResponse::success([],'', 204);
    }
}
