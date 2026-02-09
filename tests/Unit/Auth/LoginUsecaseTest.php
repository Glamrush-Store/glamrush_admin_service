<?php

use App\Domain\Auth\Actions\AuthenticateUserAction;
use App\Domain\Auth\Actions\IssueDeviceTokenAction;
use App\Domain\Auth\UseCases\LoginUsecase;
use App\Models\User;

// use Mockery;

it('authenticates user and issues device token', function () {
    // Arrange
    $user = Mockery::mock(User::class);

    $authenticate = Mockery::mock(AuthenticateUserAction::class);
    $authenticate
        ->shouldReceive('run')
        ->once()
        ->with('john@example.com', 'password')
        ->andReturn($user);

    $issueToken = Mockery::mock(IssueDeviceTokenAction::class);
    $issueToken
        ->shouldReceive('run')
        ->once()
        ->with($user, 'device-id-123', 'iphone')
        ->andReturn('token-abc');

    $usecase = new LoginUsecase($authenticate, $issueToken);

    // Act
    $result = $usecase->execute([
        'email' => 'john@example.com',
        'password' => 'password',
        'device_id' => 'device-id-123',
        'device_name' => 'iphone',
    ]);

    // Assert
    expect($result)->toBe([
        'access_token' => 'token-abc',
        'token_type' => 'Bearer',
    ]);
});

it('uses default device name when none is provided', function () {
    $user = Mockery::mock(User::class);

    $authenticate = Mockery::mock(AuthenticateUserAction::class);
    $authenticate
        ->shouldReceive('run')
        ->once()
        ->andReturn($user);

    $issueToken = Mockery::mock(IssueDeviceTokenAction::class);
    $issueToken
        ->shouldReceive('run')
        ->once()
        ->with($user, 'device-id-123', 'unknown-device')
        ->andReturn('token-abc');

    $usecase = new LoginUsecase($authenticate, $issueToken);

    $result = $usecase->execute([
        'email' => 'john@example.com',
        'password' => 'password',
        'device_id' => 'device-id-123',
        // device_name intentionally missing
    ]);

    expect($result['access_token'])->toBe('token-abc');
});

afterEach(function () {
    Mockery::close();
});
