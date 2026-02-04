<?php

use App\Usecases\Auth\CreateAccountUsecase;
use App\Actions\Auth\CreateUserAction;
use App\Actions\Auth\IssueDeviceTokenAction;
use App\Models\User;
//use Mockery;


    it('creates a user and issues a device token', function () {
        // Arrange
        $user = Mockery::mock(User::class);

        $createUser = Mockery::mock(CreateUserAction::class);
        $createUser
            ->shouldReceive('run')
            ->once()
            ->with(
                'John Doe',
                'john@example.com',
                'password',
                'admin'
            )
            ->andReturn($user);

        $issueToken = Mockery::mock(IssueDeviceTokenAction::class);
        $issueToken
            ->shouldReceive('run')
            ->once()
            ->with($user, 'device-id-123', 'iphone')
            ->andReturn('token-abc');

        $usecase = new CreateAccountUsecase($createUser, $issueToken);

        // Act
        $result = $usecase->execute([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password',
            'role' => 'admin',
            'device_id' => 'device-id-123',
            'device_name' => 'iphone',
        ]);

        // Assert
        expect($result)->toBe([
            'user' => $user,
            'access_token' => 'token-abc',
            'token_type' => 'Bearer',
        ]);

});
