<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Exceptions\Auth;

use App\Const\Auth\AuthMessages;
use RuntimeException;

class InvalidResetCodeException extends RuntimeException {
    protected $message = AuthMessages::INVALID_RESET_CODE;
}
