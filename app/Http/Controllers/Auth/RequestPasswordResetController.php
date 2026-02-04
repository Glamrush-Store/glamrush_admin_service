<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RequestPasswordResetRequest;
use App\Usecases\Auth\RequestPasswordResetUsecase;

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
