<?php
use App\Usecases\Auth\VerifyPasswordResetCodeUsecase;
use App\Actions\Auth\ValidateResetCodeAction;
use App\Actions\Auth\IssuePasswordResetTokenAction;
use App\Models\User;

it('validates reset code and issues a password reset token', function () {
    // Arrange
    $user = Mockery::mock(User::class);

    $validateCode = Mockery::mock(ValidateResetCodeAction::class);
    $validateCode
        ->shouldReceive('run')
        ->once()
        ->with('john@example.com', '123456')
        ->andReturn($user);

    $issueToken = Mockery::mock(IssuePasswordResetTokenAction::class);
    $issueToken
        ->shouldReceive('run')
        ->once()
        ->with($user)
        ->andReturn('reset-token-abc');

    $usecase = new VerifyPasswordResetCodeUsecase(
        $validateCode,
        $issueToken
    );

    // Act
    $result = $usecase->execute([
        'email' => 'john@example.com',
        'code' => '123456',
    ]);

    // Assert
    expect($result)->toBe([
        'reset_token' => 'reset-token-abc',
    ]);
});
