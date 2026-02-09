<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Auth\Actions;

use App\Models\User;

class FindUserByEmailAction
{
    public function run(string $email): User
    {
        try {
            $user = User::where('email', $email)->firstOrFail();
        } catch (\Throwable $e) {
            throw new \RuntimeException('User not found', 0, $e);
        }

        return $user;

    }
}
