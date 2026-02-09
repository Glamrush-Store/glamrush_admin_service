<?php

namespace App\Http\Controllers\Auth;

use App\Domain\Auth\UseCases\RequestPasswordResetUsecase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RequestPasswordResetRequest;

class RequestPasswordResetController extends Controller
{
    public function __invoke(
        RequestPasswordResetRequest $request,
        RequestPasswordResetUsecase $usecase
    ) {
        $result = $usecase->execute($request->validated());

        return response()->json([
            'message' => 'Reset code sent if account exists',
        ]);
    }
}
