<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Auth\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UpdateUserPasswordAction
{
    public function run(User $user, string $password): void
    {
        try {
            $user->update([
                'password' => Hash::make($password),
            ]);
        } catch (\throwable $e) {
            throw new \RuntimeException('Password update failed', 0, $e);
        }

    }
}
