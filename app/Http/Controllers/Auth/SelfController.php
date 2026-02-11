<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Controllers\Auth;

use App\Http\Responses\ApiResponse;

class SelfController
{
    public function __invoke()
    {

        $user = auth()->user();

        return ApiResponse::success($user, 'OK', 200);

    }
}
