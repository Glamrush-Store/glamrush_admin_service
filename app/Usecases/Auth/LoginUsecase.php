<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Usecases\Auth;

use App\Actions\Auth\AuthenticateUserAction;
use App\Actions\Auth\IssueDeviceTokenAction;

readonly class LoginUsecase
{
    public function __construct(
        private  AuthenticateUserAction $authenticate,
        private  IssueDeviceTokenAction $issueToken
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
