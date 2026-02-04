<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

use App\Http\Controllers\Auth\ConfirmPasswordResetController;
use App\Http\Controllers\Auth\CreateAccountController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RequestPasswordResetController;
use App\Http\Controllers\Auth\SelfController;
use App\Http\Controllers\Auth\VerifyPasswordResetCodeController;
use Illuminate\Support\Facades\Route;

// ========================================================
// PUBLIC API ROUTES
// ========================================================

Route::prefix('v1')->group(function () {
    Route::get('/health', fn () => response()->json(['status' => 'ok']));
    Route::post('/account/create', CreateAccountController::class)->middleware('auth:sanctum', 'permission:Create_User');
    Route::post('/account/login', LoginController::class);
    Route::post('/account/logout', LogoutController::class)->middleware('auth:sanctum');
    Route::post('/password/reset/request', RequestPasswordResetController::class);
    Route::post('/password/reset/verify', VerifyPasswordResetCodeController::class);
    Route::post('/password/reset/confirm', ConfirmPasswordResetController::class)->middleware(['auth:sanctum', 'ability:password:reset']);
    Route::get('/whoami', SelfController::class)->middleware(['auth:sanctum']);
});

// ========================================================
//  VERIFY PASSWORD RESET
// ========================================================

Route::get('/crash', function () {
    throw new Exception('Intentional crash');
});

Route::get('/_panic', function () {
    abort(418, 'I am a teapot');
});
