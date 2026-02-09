<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Controllers\Auth;

use App\Domain\Auth\UseCases\CreateAccountUsecase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\CreateAccountRequest;
use App\Http\Responses\ApiResponse;

class CreateAccountController extends Controller
{
    public function __invoke(CreateAccountRequest $request, CreateAccountUsecase $usecase)
    {
        try {
            $result = $usecase->execute($request->validated());
        } catch (\Throwable $e) {
            return ApiResponse::error($e->getMessage(), [], 400);
        }

        return ApiResponse::success($result, 'OK', 201);

    }
}
