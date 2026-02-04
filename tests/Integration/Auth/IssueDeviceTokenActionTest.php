<?php
/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */


use App\Actions\Auth\IssueDeviceTokenAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

it('issues a device token with the correct device ability', function () {
    $user = User::factory()->create();

    $action = app(IssueDeviceTokenAction::class);

    $token = $action->run($user, 'device-123', 'iPhone 15');

    expect($token)->toBeString();

    $storedToken = PersonalAccessToken::first();

    expect($storedToken)->not->toBeNull();
    expect($storedToken->tokenable_id)->toBe($user->id);
    expect($storedToken->can('device:device-123'))->toBeTrue();
});


it('revokes existing tokens for the same device before issuing a new one', function () {
    $user = User::factory()->create();

    // Existing token for same device
    $user->createToken('Old Phone', ['device:device-123']);

    // Token for another device (must survive)
    $user->createToken('Tablet', ['device:tablet-999']);

    $action = app(IssueDeviceTokenAction::class);

    $action->run($user, 'device-123', 'New Phone');

    $tokens = PersonalAccessToken::where('tokenable_id', $user->id)->get();

    expect($tokens)->toHaveCount(2);

    expect(
        $tokens->contains(fn ($t) => $t->can('device:device-123'))
    )->toBeTrue();

    expect(
        $tokens->contains(fn ($t) => $t->can('device:tablet-999'))
    )->toBeTrue();
});
