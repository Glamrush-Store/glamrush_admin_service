<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Const\Auth;

final class AuthMessages
{
    // ========================================================
    // ERROR MESSAGES
    // ========================================================
    public const INVALID_RESET_CODE = 'Invalid reset code';

    public const FAILED_PASSWORD_RESET = 'Failed to reset password';

    public const LOGIN_FAIL = 'invalid username or password';

    // ========================================================
    // SUCCESS MESSAGES
    // ========================================================

    public const RESET_CODE_SENT = 'Reset code sent';

    public const PASSWORD_RESET_SUCCESS = 'Password updated successfully';
}
