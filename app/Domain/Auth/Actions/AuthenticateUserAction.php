<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Auth\Actions;

use App\Const\Auth\AuthMessages;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
USE App\Exceptions\BusinessException;

class AuthenticateUserAction
{
    public function run(string $email, string $password): ?User
    {
        $user = User::where('email', $email)->first();


        if (!$user || ! Hash::check($password, $user->password)) {
            throw new BusinessException(AuthMessages::LOGIN_FAIL);
        }

        return $user;
    }
}
