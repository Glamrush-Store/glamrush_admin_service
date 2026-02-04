<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Actions\Auth;

use App\Models\User;

class RevokeAllTokensAction
{
    public function run(User $user): void
    {
        try {
            $user->tokens()->delete();
        } catch (\throwable $e) {
            throw new \RuntimeException('Failed to revoke token', 0, $e);
        }

    }
}
