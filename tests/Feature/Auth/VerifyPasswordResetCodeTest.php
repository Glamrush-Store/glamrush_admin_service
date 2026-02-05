<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

use App\Models\PasswordResetCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('validates reset code and returns a reset token', function () {
    $user = User::factory()->create([
        'email' => 'john@example.com',
    ]);

    PasswordResetCode::factory()
        ->for($user)
        ->withCode('123456')
        ->create();

    $response = $this->postJson('/api/v1/password/reset/verify', [
        'email' => 'john@example.com',
        'code' => '123456',
    ]);

    $response
        ->assertOk()
        ->assertJsonStructure([
            'success',
            'message',
            'data' => ['reset_token'],
        ]);
});

it('rejects expired reset code', function () {
    $user = User::factory()->create([
        'email' => 'john@example.com',
    ]);

    PasswordResetCode::factory()
        ->for($user)
        ->expired()
        ->withCode('123456')
        ->create();

    $response = $this->postJson('/api/v1/password/reset/verify', [
        'email' => 'john@example.com',
        'code' => '123456',
    ]);

    $response
        ->assertStatus(400)
        ->assertJson([
            'success' => false,
            'message' => \App\Const\Auth\AuthMessages::INVALID_RESET_CODE,
        ]);
});
