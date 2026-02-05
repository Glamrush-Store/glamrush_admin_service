<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('logs out the user and invalidates the token', function () {
    $user = User::factory()->create();

    // issue token
    $token = $user->createToken('test')->plainTextToken;

    // logout
    $logoutResponse = $this
        ->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/account/logout');

    $logoutResponse->assertOk();

    // try to access protected route again
    $meResponse = $this
        ->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/whoami');

    $meResponse->assertStatus(401);
});
