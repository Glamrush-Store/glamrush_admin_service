<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

use App\Domain\Auth\Actions\ValidateResetCodeAction;
use App\Exceptions\Auth\InvalidResetCodeException;
use App\Models\PasswordResetCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
uses(Tests\TestCase::class);

it('returns user and marks code as verified when code is valid', function () {
    $user = User::factory()->create([
        'email' => 'john@example.com',
    ]);

    PasswordResetCode::factory()
        ->for($user)
        ->withCode('123456')
        ->create([
            'expires_at' => now()->addMinutes(10),
        ]);

    $action = app(ValidateResetCodeAction::class);

    $result = $action->run('john@example.com', '123456');

    expect($result->is($user))->toBeTrue();

    $code = PasswordResetCode::first();
    expect($code->verified_at)->not->toBeNull();
});

it('throws when reset code is invalid', function () {
    $user = User::factory()->create();

    PasswordResetCode::factory()
        ->for($user)
        ->withCode('123456')
        ->create();

    $action = app(ValidateResetCodeAction::class);

    expect(fn () => $action->run($user->email, '000000'))
        ->toThrow(InvalidResetCodeException::class);
});

it('throws when reset code is expired', function () {
    $user = User::factory()->create();

    PasswordResetCode::factory()
        ->for($user)
        ->expired()
        ->withCode('123456')
        ->create();

    $action = app(ValidateResetCodeAction::class);

    expect(fn () => $action->run($user->email, '123456'))
        ->toThrow(InvalidResetCodeException::class);
});

it('throws when reset code has already been used', function () {
    $user = User::factory()->create();

    PasswordResetCode::factory()
        ->for($user)
        ->verified()
        ->withCode('123456')
        ->create();

    $action = app(ValidateResetCodeAction::class);

    expect(fn () => $action->run($user->email, '123456'))
        ->toThrow(InvalidResetCodeException::class);
});

it('throws when user does not exist', function () {
    $action = app(ValidateResetCodeAction::class);

    expect(fn () => $action->run('ghost@example.com', '123456'))
        ->toThrow(InvalidResetCodeException::class);
});
