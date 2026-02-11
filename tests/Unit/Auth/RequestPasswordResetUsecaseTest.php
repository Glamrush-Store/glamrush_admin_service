<?php

use App\Domain\Auth\Actions\FindUserByEmailAction;
use App\Domain\Auth\Actions\GenerateResetCodeAction;
use App\Domain\Auth\Actions\SendResetCodeMailAction;
use App\Domain\Auth\UseCases\RequestPasswordResetUsecase;
use App\Models\User;

it('finds user, generates reset code, and sends reset email', function () {
    // Arrange
    $user = Mockery::mock(User::class);
    $code = '123456';

    $findUser = Mockery::mock(FindUserByEmailAction::class);
    $generateCode = Mockery::mock(GenerateResetCodeAction::class);
    $sendMail = Mockery::mock(SendResetCodeMailAction::class);

    $findUser
        ->shouldReceive('run')
        ->once()
        ->with('john@example.com')
        ->andReturn($user)
        ->ordered();

    $generateCode
        ->shouldReceive('run')
        ->once()
        ->with($user)
        ->andReturn($code)
        ->ordered();

    $sendMail
        ->shouldReceive('run')
        ->once()
        ->with($user, $code)
        ->ordered();

    $usecase = new RequestPasswordResetUsecase(
        $findUser,
        $generateCode,
        $sendMail
    );

    // Act
    $result = $usecase->execute([
        'email' => 'john@example.com',
    ]);

    // Assert
    expect($result)->toBeNull(); // void method
});
