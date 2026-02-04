<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Usecases\Auth;

use App\Actions\Auth\FindUserByEmailAction;
use App\Actions\Auth\GenerateResetCodeAction;
use App\Actions\Auth\SendResetCodeMailAction;

readonly class RequestPasswordResetUsecase
{
    public function __construct(
        private  FindUserByEmailAction $findUser,
        private  GenerateResetCodeAction $generateCode,
        private  SendResetCodeMailAction $sendMail
    ) {}

    public function execute(array $data): void
    {
        $user = $this->findUser->run($data['email']);

        $code = $this->generateCode->run($user);
        $this->sendMail->run($user, $code);
    }
}
