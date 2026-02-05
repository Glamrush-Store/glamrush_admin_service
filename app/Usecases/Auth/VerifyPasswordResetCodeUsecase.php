<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Usecases\Auth;

use App\Actions\Auth\IssuePasswordResetTokenAction;
use App\Actions\Auth\ValidateResetCodeAction;

readonly class VerifyPasswordResetCodeUsecase
{
    public function __construct(
        private ValidateResetCodeAction $validateCode,
        private IssuePasswordResetTokenAction $issueToken
    ) {}

    public function execute(array $data): array
    {
        $user = $this->validateCode->run(
            $data['email'],
            $data['code']
        );

        return [
            'reset_token' => $this->issueToken->run($user),
        ];
    }
}
