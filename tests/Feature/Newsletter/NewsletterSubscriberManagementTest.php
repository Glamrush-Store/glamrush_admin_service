<?php

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    Schema::create('newsletter_subscribers', function (Blueprint $table): void {
        $table->ulid('id')->primary();
        $table->string('email')->unique();
        $table->string('status', 20)->index();
        $table->string('source', 100)->nullable();
        $table->string('confirmation_token_hash', 64)->nullable()->unique();
        $table->string('unsubscribe_token_hash', 64)->unique();
        $table->timestampTz('confirmation_expires_at')->nullable();
        $table->timestampTz('consented_at')->nullable();
        $table->timestampTz('confirmed_at')->nullable();
        $table->timestampTz('unsubscribed_at')->nullable();
        $table->string('consent_ip_hash', 64)->nullable();
        $table->string('consent_user_agent', 500)->nullable();
        $table->timestampsTz();
    });
});

function newsletterAdmin(array $permissions): User
{
    $user = User::factory()->create();
    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'sanctum']);
    }
    $user->givePermissionTo($permissions);
    Sanctum::actingAs($user);

    return $user;
}

function newsletterSubscriber(array $attributes = []): string
{
    $id = (string) Str::ulid();
    DB::table('newsletter_subscribers')->insert(array_merge([
        'id' => $id,
        'email' => Str::lower(Str::random(8)).'@example.com',
        'status' => 'pending',
        'source' => 'storefront-footer',
        'confirmation_token_hash' => hash('sha256', Str::random()),
        'unsubscribe_token_hash' => hash('sha256', Str::random()),
        'confirmation_expires_at' => now()->addHour(),
        'consented_at' => now()->subDay(),
        'confirmed_at' => null,
        'unsubscribed_at' => null,
        'consent_ip_hash' => hash('sha256', '127.0.0.1'),
        'consent_user_agent' => 'Sensitive browser data',
        'created_at' => now(),
        'updated_at' => now(),
    ], $attributes));

    return $id;
}

it('requires authentication and the appropriate viewing permission', function () {
    $this->getJson('/api/v1/newsletter/subscribers')->assertUnauthorized();

    Sanctum::actingAs(User::factory()->create());
    $this->getJson('/api/v1/newsletter/subscribers')->assertForbidden();
});

it('lists paginated subscribers with safe fields and filters', function () {
    newsletterAdmin(['ViewAny_NewsletterSubscriber']);
    newsletterSubscriber([
        'email' => 'amber@example.com',
        'status' => 'subscribed',
        'source' => 'campaign-modal',
        'confirmed_at' => '2026-08-02 12:00:00',
        'created_at' => '2026-08-01 12:00:00',
    ]);
    newsletterSubscriber([
        'email' => 'musk@example.com',
        'status' => 'unsubscribed',
        'source' => 'storefront-footer',
        'confirmed_at' => '2026-07-01 12:00:00',
        'created_at' => '2026-06-01 12:00:00',
    ]);

    $this->getJson('/api/v1/newsletter/subscribers?search=amber&status=subscribed&source=campaign-modal&confirmed_from=2026-08-01&confirmed_to=2026-08-02&created_from=2026-08-01&created_to=2026-08-01&sort_by=email&sort_dir=asc&per_page=1')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.email', 'amber@example.com')
        ->assertJsonPath('meta.per_page', 1)
        ->assertJsonMissingPath('data.0.confirmation_token_hash')
        ->assertJsonMissingPath('data.0.unsubscribe_token_hash')
        ->assertJsonMissingPath('data.0.consent_ip_hash')
        ->assertJsonMissingPath('data.0.consent_user_agent');
});

it('rejects unsafe sorting and excessive page sizes', function () {
    newsletterAdmin(['ViewAny_NewsletterSubscriber']);

    $this->getJson('/api/v1/newsletter/subscribers?sort_by=email%20desc%3Bdrop%20table&sort_dir=sideways&per_page=1000')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['sort_by', 'sort_dir', 'per_page']);
});

it('shows a subscriber safely and returns 404 for a missing subscriber', function () {
    newsletterAdmin(['View_NewsletterSubscriber']);
    $id = newsletterSubscriber(['email' => 'detail@example.com']);

    $this->getJson("/api/v1/newsletter/subscribers/{$id}")
        ->assertOk()
        ->assertJsonPath('data.email', 'detail@example.com')
        ->assertJsonMissingPath('data.confirmation_token_hash');

    $this->getJson('/api/v1/newsletter/subscribers/'.Str::ulid())->assertNotFound();
});

it('checks export permission separately', function () {
    newsletterAdmin(['ViewAny_NewsletterSubscriber']);

    $this->getJson('/api/v1/newsletter/subscribers/export')->assertForbidden();
});

it('streams a filtered subscribed-only injection-safe CSV', function () {
    newsletterAdmin(['Export_NewsletterSubscriber']);
    newsletterSubscriber([
        'email' => 'kept@example.com',
        'status' => 'subscribed',
        'source' => '=campaign',
        'confirmed_at' => '2026-08-03 12:00:00',
    ]);
    newsletterSubscriber([
        'email' => 'other@example.com',
        'status' => 'subscribed',
        'source' => 'other',
        'confirmed_at' => '2026-08-03 12:00:00',
    ]);
    newsletterSubscriber(['email' => 'pending@example.com', 'status' => 'pending', 'source' => '=campaign']);
    newsletterSubscriber(['email' => 'gone@example.com', 'status' => 'unsubscribed', 'source' => '=campaign']);

    $response = $this->get('/api/v1/newsletter/subscribers/export?source=%3Dcampaign&confirmed_from=2026-08-01&confirmed_to=2026-08-04');
    $response->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');

    expect($response->headers->get('content-disposition'))
        ->toMatch('/newsletter-subscribers-\d{4}-\d{2}-\d{2}-\d{6}\.csv/');

    $csv = $response->streamedContent();
    expect($csv)
        ->toStartWith("email,source,consented_at,confirmed_at\n")
        ->toContain("kept@example.com,'=campaign")
        ->not->toContain('pending@example.com')
        ->not->toContain('gone@example.com')
        ->not->toContain('other@example.com');
    expect(substr_count($csv, 'kept@example.com'))->toBe(1);
});
