<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Auth\UseCases;

use App\Domain\Auth\Actions\RevokeAllTokensAction;
use App\Domain\Auth\Actions\UpdateUserPasswordAction;
use App\Models\User;

readonly class ConfirmPasswordResetUsecase
{
    public function __construct(
        private UpdateUserPasswordAction $updatePassword,
        private RevokeAllTokensAction $revokeTokens
    ) {}

    public function execute(User $user, string $password): void
    {
        $this->updatePassword->run($user, $password);
        $this->revokeTokens->run($user);
    }
}
