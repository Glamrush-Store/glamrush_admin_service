<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Controllers\Auth;

use App\Http\Responses\ApiResponse;
use Illuminate\Http\Request;

class LogoutController
{
    public function __invoke(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        ApiResponse::success([], ' User logged out');
    }
}
