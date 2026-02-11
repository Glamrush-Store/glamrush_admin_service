<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Auth\Actions;

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
