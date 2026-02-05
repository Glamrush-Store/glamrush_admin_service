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
        try {
            $result = auth()->userOrFail();

            return ApiResponse::success($result, 'OK', 200);
        } catch (\Exception $e) {
            return ApiResponse::error('Unauthorized', [], 401);
        }

    }
}
