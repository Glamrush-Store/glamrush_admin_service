<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Actions\Auth;

use App\Models\User;

class IssuePasswordResetTokenAction
{
    public function run(User $user): ?string
    {
        return $user->createToken(
            'password-reset',
            ['password:reset']
        )->plainTextToken;
    }
}
