<?php

use App\Domain\Auth\Actions\CreateUserAction;
use App\Domain\Auth\Actions\IssueDeviceTokenAction;
use App\Domain\Auth\UseCases\CreateAccountUsecase;
use App\Models\User;

// use Mockery;
it('creates an account and returns user with token', function () {

    $user = Mockery::mock(User::class);
    $token = 'test-access-token';

    $createUser = Mockery::mock(CreateUserAction::class);
    $createUser
        ->shouldReceive('run')
        ->once()
        ->with('John', 'john@example.com', 'secret', 'user')
        ->andReturn($user);

    $issueToken = Mockery::mock(IssueDeviceTokenAction::class);
    $issueToken
        ->shouldReceive('run')
        ->once()
        ->with($user, 'device-123', 'iPhone')
        ->andReturn($token);

    $usecase = new CreateAccountUsecase(
        $createUser,
        $issueToken
    );

    $result = $usecase->execute([
        'name' => 'John',
        'email' => 'john@example.com',
        'password' => 'secret',
        'role' => 'user',
        'device_id' => 'device-123',
        'device_name' => 'iPhone',
    ]);

    expect($result)->toBe([
        'user' => $user,
        'access_token' => $token,
        'token_type' => 'Bearer',
    ]);
});
