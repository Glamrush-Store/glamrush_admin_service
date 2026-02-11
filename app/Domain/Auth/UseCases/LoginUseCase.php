<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Auth\UseCases;

use App\Domain\Auth\Actions\AuthenticateUserAction;
use App\Domain\Auth\Actions\IssueDeviceTokenAction;

readonly class LoginUseCase
{
    public function __construct(
        private AuthenticateUserAction $authenticate,
        private IssueDeviceTokenAction $issueToken
    ) {}

    public function execute(array $data): array|string|null
    {
        $user = $this->authenticate->run(
            $data['email'],
            $data['password']
        );

        $token = $this->issueToken->run(
            $user,
            $data['device_id'],
            $data['device_name'] ?? 'unknown-device'
        );

        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
        ];
    }
}
