<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

use App\Actions\Auth\FindUserByEmailAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

it('should find user by email', function () {

    $email = 'demilade@gmail.com';

    $user = User::factory()->create([
        'email' => $email,
    ]);

    $action = app(FindUserByEmailAction::class);

    $result = $action->run($email);

    expect($result)->toBeInstanceOf(User::class);

});

it('should fail when user email is not found', function () {

    $email = 'demilade@gmail.com';

    $user = User::factory()->create([
        'email' => $email,
    ]);

    $action = app(FindUserByEmailAction::class);

    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage('User not found');

    $result = $action->run('awrongemail@example.com');

});
