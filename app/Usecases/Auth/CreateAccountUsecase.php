<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Usecases\Auth;

use App\Actions\Auth\CreateUserAction;
use App\Actions\Auth\IssueDeviceTokenAction;

readonly class CreateAccountUsecase
{
    public function __construct(
        private  CreateUserAction $createUser,
        private  IssueDeviceTokenAction $issueToken
    ) {}

    public function execute(array $data): array
    {
        $user = $this->createUser->run(
            $data['name'],
            $data['email'],
            $data['password'],
            $data['role']
        );

        $token = $this->issueToken->run(
            $user,
            $data['device_id'],
            $data['device_name'] ?? 'unknown-device'
        );

        return [
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ];
    }
}
