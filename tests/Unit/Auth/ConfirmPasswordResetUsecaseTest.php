<?php

use App\Domain\Auth\Actions\RevokeAllTokensAction;
use App\Domain\Auth\Actions\UpdateUserPasswordAction;
use App\Domain\Auth\UseCases\ConfirmPasswordResetUsecase;
use App\Models\User;

it('updates user password', function () {

    $user = Mockery::mock(User::class);

    $UpdateUserPassword = Mockery::mock(UpdateUserPasswordAction::class);
    $UpdateUserPassword->shouldReceive('run')->once()->with($user, 'new-password')->andReturn($user);

    $RevokeAllTokens = Mockery::mock(RevokeAllTokensAction::class);
    $RevokeAllTokens->shouldReceive('run')->once()->with($user);

    $usecase = new ConfirmPasswordResetUsecase($UpdateUserPassword, $RevokeAllTokens);

    $result = $usecase->execute($user, 'new-password');

    // Assert
    expect($result)->toBeNull(); // void method

});
