<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

use App\Domain\Auth\Actions\CreateUserAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Exceptions\RoleDoesNotExist;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

it('creates a user and assigns the given role', function () {
    // Arrange
    Role::create(['name' => 'admin']);

    $action = app(CreateUserAction::class);

    // Act
    $user = $action->run(
        name: 'John Doe',
        email: 'john@example.com',
        password: 'secret123',
        role: 'admin'
    );

    // Assert: user exists
    expect($user)->toBeInstanceOf(User::class);
    expect($user->email)->toBe('john@example.com');

    // Assert: password is hashed
    expect(Hash::check('secret123', $user->password))->toBeTrue();

    // Assert: role assigned
    expect($user->hasRole('admin'))->toBeTrue();

    // Assert: persisted
    expect(User::where('email', 'john@example.com')->exists())->toBeTrue();
});

it('throws runtime exception when user creation fails', function () {
    $action = app(CreateUserAction::class);

    $this->expectException(RoleDoesNotExist::class);

    $action->run(
        name: 'John Doe',
        email: 'not-an-email', // triggers DB or validation failure
        password: 'secret',
        role: 'non-existent-role'
    );
});
