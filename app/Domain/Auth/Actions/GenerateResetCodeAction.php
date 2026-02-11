<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Auth\Actions;

use App\Exceptions\BusinessException;
use App\Models\PasswordResetCode;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class GenerateResetCodeAction
{
    public function run(User $user): string
    {

            $code = '777777'; // random_int(100000, 999999);

            PasswordResetCode::where('user_id', $user->id)->delete();

            PasswordResetCode::create([
                'user_id' => $user->id,
                'code_hash' => Hash::make($code),
                'expires_at' => now()->addMinutes(15),
            ]);


        return (string) $code;
    }
}
