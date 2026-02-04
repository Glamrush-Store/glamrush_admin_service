<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Actions\Auth;

use App\Const\Auth\AuthMessages;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthenticateUserAction
{
    public function run(string $email, string $password): ?User
    {
        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw new \RuntimeException(AuthMessages::LOGIN_FAIL);
        }

        return $user;
    }
}
