<?php

use App\Mail\Settings\SettingsTestEmail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

function settingsAdmin(array $permissions): User
{
    $user = User::factory()->create();

    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'sanctum']);
    }

    $user->givePermissionTo($permissions);
    Sanctum::actingAs($user);

    return $user;
}

it('requires authentication and settings update permission to send a test email', function () {
    $this->postJson('/api/v1/settings/test-email', ['email' => 'ops@example.com'])->assertUnauthorized();

    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/v1/settings/test-email', ['email' => 'ops@example.com'])->assertForbidden();
});

it('sends a test email using the current mail configuration', function () {
    Mail::fake();
    settingsAdmin(['Update_Setting']);

    config([
        'mail.default' => 'resend',
        'mail.from.address' => 'admin@glamrush.test',
    ]);

    $this->postJson('/api/v1/settings/test-email', [
        'email' => 'ops@example.com',
        'name' => 'Ops Team',
    ])
        ->assertOk()
        ->assertJsonPath('message', 'Test email sent')
        ->assertJsonPath('data.email', 'ops@example.com')
        ->assertJsonPath('data.mailer', 'resend')
        ->assertJsonPath('data.from_address', 'admin@glamrush.test');

    Mail::assertSent(SettingsTestEmail::class, function (SettingsTestEmail $mail): bool {
        return $mail->hasTo('ops@example.com')
            && $mail->recipientName === 'Ops Team'
            && $mail->mailerName === 'resend';
    });
});

it('validates the recipient email before sending', function () {
    Mail::fake();
    settingsAdmin(['Update_Setting']);

    $this->postJson('/api/v1/settings/test-email', ['email' => 'not-an-email'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');

    Mail::assertNothingSent();
});

