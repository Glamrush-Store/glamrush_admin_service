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
    public function __construct(private CreateAccountUsecase $usecase) {}

    public function __invoke(CreateAccountRequest $request)
    {

        $result = $this->usecase->execute($request->validated());

        return ApiResponse::success($result, 'Account Created', 201);

    }
}
