<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace Database\Factories;

use App\Models\PasswordResetCode;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class PasswordResetCodeFactory extends Factory
{
    protected $model = PasswordResetCode::class;

    /**
     * Default raw code for tests.
     * Accessible via ->rawCode() helper.
     */
    private string $plainCode;

    public function definition(): array
    {
        $this->plainCode = str_pad(
            (string) random_int(0, 999999),
            6,
            '0',
            STR_PAD_LEFT
        );

        return [
            'user_id' => User::factory(),
            'code_hash' => Hash::make($this->plainCode),
            'expires_at' => now()->addMinutes(10),
            'verified_at' => null,
        ];
    }

    /**
     * Override the reset code with a known value.
     */
    public function withCode(string $code): static
    {
        return $this->state(function () use ($code) {
            return [
                'code_hash' => Hash::make($code),
            ];
        });
    }

    /**
     * Mark code as expired.
     */
    public function expired(): static
    {
        return $this->state(fn () => [
            'expires_at' => now()->subMinute(),
        ]);
    }

    /**
     * Mark code as already verified.
     */
    public function verified(): static
    {
        return $this->state(fn () => [
            'verified_at' => now(),
        ]);
    }
}
