<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Auth\Actions;

use App\Models\User;

class SendResetCodeMailAction
{
    public function run(User $user, string $code): void
    {
        // TODO LATER

    }
}
