<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

use App\Domain\Auth\Actions\GenerateResetCodeAction;
use App\Models\PasswordResetCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

it('generates a reset code and stores its hash', function () {
    $user = User::factory()->create();

    $action = app(GenerateResetCodeAction::class);

    $code = $action->run($user);

    // returned code looks right
    expect($code)->toBeString();
    expect(strlen($code))->toBe(6);

    $record = PasswordResetCode::where('user_id', $user->id)->first();

    // record exists
    expect($record)->not->toBeNull();

    // code is hashed
    expect(Hash::check($code, $record->code_hash))->toBeTrue();

    // expiry is set
    expect($record->expires_at->isAfter(now()))->toBeTrue();

});

it('deletes existing reset codes before creating a new one', function () {
    $user = User::factory()->create();

    // existing (old) code
    PasswordResetCode::factory()->create([
        'user_id' => $user->id,
    ]);

    $action = app(GenerateResetCodeAction::class);

    $action->run($user);

    $codes = PasswordResetCode::where('user_id', $user->id)->get();

    // only one reset code should exist
    expect($codes)->toHaveCount(1);
});

it('throws runtime exception when generation fails', function () {
    $user = User::factory()->create();

    // force failure by mocking random_int
    $this->partialMock(GenerateResetCodeAction::class, function ($mock) {
        $mock->shouldAllowMockingProtectedMethods()
            ->shouldReceive('run')
            ->andThrow(new RuntimeException('Reset code generation failed'));
    });

    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage('Reset code generation failed');

    app(GenerateResetCodeAction::class)->run($user);
});
