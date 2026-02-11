<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Controllers\Auth;

use App\Const\Auth\AuthMessages;
use App\Domain\Auth\UseCases\ConfirmPasswordResetUsecase;
use App\Http\Requests\Auth\ConfirmPasswordResetRequest;
use App\Http\Responses\ApiResponse;

class ConfirmPasswordResetController
{
    public function __construct(private ConfirmPasswordResetUsecase $usecase) {}

    public function __invoke(
        ConfirmPasswordResetRequest $request,
    ) {
        $this->usecase->execute(
            $request->user(),
            $request->validated()['password']
        );

        return ApiResponse::success([], AuthMessages::PASSWORD_RESET_SUCCESS, 200);
    }
}
