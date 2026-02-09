<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Auth\Actions;

use App\Models\User;

class IssueDeviceTokenAction
{
    public function run(User $user, string $deviceId, string $deviceName): string
    {
        $user->tokens()
            ->whereJsonContains('abilities', 'device:'.$deviceId)
            ->delete();

        return $user->createToken(
            $deviceName,
            ['device:'.$deviceId]
        )->plainTextToken;
    }
}
