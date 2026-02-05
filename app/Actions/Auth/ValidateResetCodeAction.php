<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Actions\Auth;

use App\Exceptions\Auth\InvalidResetCodeException;
use App\Models\PasswordResetCode;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ValidateResetCodeAction
{
    public function run(string $email, string $code): User
    {
        $user = User::where('email', $email)->first();
        if (! $user) {
            throw new InvalidResetCodeException;
        }

        $record = PasswordResetCode::where('user_id', $user->id)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->first();

        if (! $record) {
            throw new InvalidResetCodeException;
        }

        if (! Hash::check($code, $record->code_hash)) {
            throw new InvalidResetCodeException;
        }

        $record->update([
            'verified_at' => now(),
        ]);

        return $user;
    }
}
