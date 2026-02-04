<?php

use App\Const\Auth\AuthMessages;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\PasswordResetCode;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);



it('allows password change using a valid reset token', function () {
    // 1️⃣ Arrange: user + reset code
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'password' => 'old-password',
    ]);

    PasswordResetCode::factory()
        ->for($user)
        ->withCode('123456')
        ->create();

    // 2️⃣ Act: validate reset code → get reset token
    $validateResponse = $this->postJson('/api/v1/password/reset/verify', [
        'email' => 'john@example.com',
        'code' => '123456',
    ]);

    $validateResponse->assertOk();

    $resetToken = $validateResponse->json('data.reset_token');

    expect($resetToken)->toBeString();

    // 3️⃣ Act: use reset token to change password
    $changeResponse = $this
        ->withHeader('Authorization', "Bearer {$resetToken}")
        ->postJson('/api/v1/password/reset/confirm', [
            'password' => 'NewPassword1',
            'password_confirmation' => 'NewPassword1',
        ]);

    $changeResponse
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => AuthMessages::PASSWORD_RESET_SUCCESS,
        ]);

    // 4️⃣ Assert: password was actually changed
    $user->refresh();

    expect(Hash::check('NewPassword1', $user->password))
        ->toBeTrue()
        ->and(Hash::check('old-password', $user->password))
        ->toBeFalse();
});
