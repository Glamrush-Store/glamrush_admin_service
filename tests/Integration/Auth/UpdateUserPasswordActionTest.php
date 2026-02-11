<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

use App\Domain\Auth\Actions\UpdateUserPasswordAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

it('updates the user password using hashing', function () {
    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
    ]);

    $action = app(UpdateUserPasswordAction::class);

    $action->run($user, 'new-password');

    $user->refresh();

    expect(Hash::check('new-password', $user->password))->toBeTrue();
});
