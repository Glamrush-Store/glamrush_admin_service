<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Auth\UseCases;

use App\Domain\Auth\Actions\CreateUserAction;
use App\Domain\Auth\Actions\IssueDeviceTokenAction;
use App\Domain\Shared\Actions\CreateAppLogAction;

readonly class CreateAccountUsecase
{
    public function __construct(
        private CreateUserAction $createUser,
        private IssueDeviceTokenAction $issueToken,
        ?CreateAppLogAction $log = null
    ) {}

    public function execute(array $data): array
    {
        try {
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
        } catch (\Throwable $e) {

            $this->log?->run(
                level: 'error',
                event: 'CREATE_ACCOUNT_FAILED',
                message: 'Failed to create user account',
                context: [
                    'data' => $data,
                    'exception' => $e->getMessage(),
                ],
                actor: auth()->user()
            );

            throw new \RuntimeException('failed to create new account', 0, $e);
        }

    }
}
