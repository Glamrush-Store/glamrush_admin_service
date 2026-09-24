<?php

use App\Models\Category;
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
    Schema::create('customer_accounts', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->string('phone')->nullable();
        $table->boolean('is_active')->default(true);
        $table->timestamp('email_verified_at')->nullable();
        $table->timestamps();
    });

    Schema::create('contact_submissions', function (Blueprint $table): void {
        $table->ulid('id')->primary();
        $table->ulid('storefront_category_id');
        $table->unsignedBigInteger('customer_account_id')->nullable();
        $table->string('name', 150);
        $table->string('email');
        $table->string('phone', 30)->nullable();
        $table->string('subject', 180)->nullable();
        $table->text('message');
        $table->string('status', 20)->default('new')->index();
        $table->string('source', 100)->nullable();
        $table->json('metadata')->nullable();
        $table->string('duplicate_fingerprint', 64);
        $table->string('deduplication_bucket', 12);
        $table->timestampTz('resolved_at')->nullable();
        $table->timestampsTz();
    });
});

function contactSubmissionAdmin(array $permissions): User
{
    $user = User::factory()->create();

    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'sanctum']);
    }

    $user->givePermissionTo($permissions);
    Sanctum::actingAs($user);

    return $user;
}

function contactSubmissionCustomer(array $attributes = []): int
{
    return (int) DB::table('customer_accounts')->insertGetId(array_merge([
        'name' => 'Jane Customer',
        'email' => 'jane.customer@example.com',
        'phone' => '+2348000000000',
        'is_active' => true,
        'email_verified_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ], $attributes));
}

function contactSubmission(array $attributes = []): string
{
    $categoryId = $attributes['storefront_category_id'] ?? Category::factory()->create()->id;
    $id = (string) Str::ulid();

    DB::table('contact_submissions')->insert(array_merge([
        'id' => $id,
        'storefront_category_id' => $categoryId,
        'customer_account_id' => null,
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'phone' => '+2348000000000',
        'subject' => 'Product question',
        'message' => 'I would like more information about this product.',
        'status' => 'new',
        'source' => 'contact-page',
        'metadata' => json_encode(['ip_address' => '127.0.0.1']),
        'duplicate_fingerprint' => hash('sha256', Str::random()),
        'deduplication_bucket' => now()->format('YmdHi'),
        'resolved_at' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ], $attributes));

    return $id;
}

it('requires authentication and list permission', function () {
    $this->getJson('/api/v1/contact-submissions')->assertUnauthorized();

    Sanctum::actingAs(User::factory()->create());
    $this->getJson('/api/v1/contact-submissions')->assertForbidden();
});

it('lists paginated submissions with filters and related summaries', function () {
    contactSubmissionAdmin(['ViewAny_ContactSubmission']);
    $fragrances = Category::factory()->create(['name' => 'Fragrances', 'slug' => 'fragrances']);
    $otherStorefront = Category::factory()->create(['name' => 'Other', 'slug' => 'other']);
    $customerId = contactSubmissionCustomer();

    contactSubmission([
        'storefront_category_id' => $fragrances->id,
        'customer_account_id' => $customerId,
        'name' => 'Amber Customer',
        'email' => 'amber@example.com',
        'status' => 'new',
        'source' => 'contact-page',
        'created_at' => '2026-09-20 12:00:00',
    ]);
    contactSubmission([
        'storefront_category_id' => $otherStorefront->id,
        'name' => 'Resolved Customer',
        'email' => 'resolved@example.com',
        'status' => 'resolved',
        'source' => 'footer',
        'resolved_at' => '2026-09-19 12:00:00',
        'created_at' => '2026-09-18 12:00:00',
    ]);

    $query = http_build_query([
        'search' => 'amber',
        'status' => 'new',
        'source' => 'contact-page',
        'storefront_category_id' => $fragrances->id,
        'customer_account_id' => $customerId,
        'created_from' => '2026-09-20',
        'created_to' => '2026-09-20',
        'sort_by' => 'name',
        'sort_dir' => 'asc',
        'per_page' => 1,
    ]);

    $this->getJson("/api/v1/contact-submissions?{$query}")
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Amber Customer')
        ->assertJsonPath('data.0.storefront.slug', 'fragrances')
        ->assertJsonPath('data.0.customer.email', 'jane.customer@example.com')
        ->assertJsonPath('meta.per_page', 1)
        ->assertJsonMissingPath('data.0.message')
        ->assertJsonMissingPath('data.0.metadata');
});

it('rejects invalid filters and unsafe sorting', function () {
    contactSubmissionAdmin(['ViewAny_ContactSubmission']);

    $this->getJson('/api/v1/contact-submissions?status=deleted&sort_by=name%20desc%3Bdrop%20table&sort_dir=sideways&per_page=1000')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['status', 'sort_by', 'sort_dir', 'per_page']);
});

it('shows a full contact submission', function () {
    contactSubmissionAdmin(['View_ContactSubmission']);
    $storefront = Category::factory()->create(['name' => 'Fragrances', 'slug' => 'fragrances']);
    $customerId = contactSubmissionCustomer();
    $id = contactSubmission([
        'storefront_category_id' => $storefront->id,
        'customer_account_id' => $customerId,
        'message' => 'This is the complete customer message for the support team.',
    ]);

    $this->getJson("/api/v1/contact-submissions/{$id}")
        ->assertOk()
        ->assertJsonPath('data.id', $id)
        ->assertJsonPath('data.message', 'This is the complete customer message for the support team.')
        ->assertJsonPath('data.metadata.ip_address', '127.0.0.1')
        ->assertJsonPath('data.storefront.slug', 'fragrances')
        ->assertJsonPath('data.customer.id', $customerId);

    $this->getJson('/api/v1/contact-submissions/'.Str::ulid())->assertNotFound();
});

it('requires update permission and validates status', function () {
    $id = contactSubmission();

    contactSubmissionAdmin(['View_ContactSubmission']);
    $this->patchJson("/api/v1/contact-submissions/{$id}/status", [
        'status' => 'resolved',
    ])->assertForbidden();

    contactSubmissionAdmin(['Update_ContactSubmission']);
    $this->patchJson("/api/v1/contact-submissions/{$id}/status", [
        'status' => 'deleted',
    ])->assertUnprocessable()->assertJsonValidationErrors('status');
});

it('updates status and keeps resolved at synchronized', function () {
    contactSubmissionAdmin(['Update_ContactSubmission']);
    $id = contactSubmission();

    $this->patchJson("/api/v1/contact-submissions/{$id}/status", [
        'status' => 'resolved',
    ])
        ->assertOk()
        ->assertJsonPath('message', 'Contact submission status updated')
        ->assertJsonPath('data.status', 'resolved')
        ->assertJsonPath('data.id', $id);

    $resolvedAt = DB::table('contact_submissions')->where('id', $id)->value('resolved_at');
    expect($resolvedAt)->not->toBeNull();

    $this->travel(1)->hour();
    $this->patchJson("/api/v1/contact-submissions/{$id}/status", [
        'status' => 'resolved',
    ])->assertOk();

    expect(DB::table('contact_submissions')->where('id', $id)->value('resolved_at'))->toBe($resolvedAt);

    $this->patchJson("/api/v1/contact-submissions/{$id}/status", [
        'status' => 'in_progress',
    ])
        ->assertOk()
        ->assertJsonPath('data.status', 'in_progress')
        ->assertJsonPath('data.resolved_at', null);

    expect(DB::table('contact_submissions')->where('id', $id)->value('resolved_at'))->toBeNull();
});
