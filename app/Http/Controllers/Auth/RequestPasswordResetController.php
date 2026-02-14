<?php

namespace App\Http\Controllers\Auth;

use App\Const\Auth\AuthMessages;
use App\Domain\Auth\UseCases\RequestPasswordResetUsecase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RequestPasswordResetRequest;
use App\Http\Responses\ApiResponse;

class RequestPasswordResetController extends Controller
{
    public function __construct(private RequestPasswordResetUsecase $usecase) {}

    public function __invoke(
        RequestPasswordResetRequest $request
    ) {

        $this->usecase->execute($request->validated());

        return ApiResponse::success([], AuthMessages::RESET_CODE_SENT);
    }
}
