<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Controllers\Auth;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Responses\ApiResponse;
use App\Usecases\Auth\LoginUsecase;

class LoginController
{
    public function __invoke(LoginRequest $request, LoginUsecase $usecase)
    {
        try {
            $response = $usecase->execute($request->validated());
            return ApiResponse::success($response);
        } catch (\Throwable $e) {
            return ApiResponse::error($e->getMessage(), [], 400);
        }
    }
}
