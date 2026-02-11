<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Controllers\Auth;

use App\Domain\Auth\UseCases\VerifyPasswordResetCodeUsecase;
use App\Http\Requests\Auth\VerifyPasswordResetCodeRequest;
use App\Http\Responses\ApiResponse;

class VerifyPasswordResetCodeController
{
    public function __construct(private VerifyPasswordResetCodeUsecase $usecase) {}

    public function __invoke(
        VerifyPasswordResetCodeRequest $request
    ) {
        $result = $this->usecase->execute($request->validated());

        return ApiResponse::success($result, 'OK', 200);
    }
}
