<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateUserAction
{
    public function run(string $name, string $email, string $password, string $role): User
    {
        try {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
            ]);

            $user->assignRole($role);
        } catch (\Throwable $e) {
            throw new \RuntimeException('User creation failed', 0, $e);
        }

        return $user;
    }
}
