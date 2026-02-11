<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Controllers\Auth;

use App\Domain\Auth\UseCases\LoginUseCase;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Responses\ApiResponse;

class LoginController
{
    public function __construct(private LoginUseCase $useCase) {}

    public function __invoke(LoginRequest $request)
    {

        $response = $this->useCase->execute($request->validated());

        return ApiResponse::success($response);

    }
}
