<?php

use App\Models\Brand;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    foreach (['View_Brand', 'Create_Brand', 'Update_Brand'] as $permission) {
        Permission::findOrCreate($permission, 'sanctum');
    }

    $user = User::factory()->create();
    $user->givePermissionTo(['View_Brand', 'Create_Brand', 'Update_Brand']);
    Sanctum::actingAs($user);
});

it('filters and sorts brands using the table query parameters', function () {
    $alpha = Brand::query()->create([
        'name' => 'Alpha Beauty',
        'slug' => 'alpha-beauty',
        'code' => 'ALPHA',
        'is_active' => false,
        'sort_order' => 3,
    ]);
    $beta = Brand::query()->create([
        'name' => 'Beta Care',
        'slug' => 'beta-care',
        'code' => 'BETA',
        'is_active' => false,
        'sort_order' => 2,
    ]);
    $active = Brand::query()->create([
        'name' => 'Active Alpha',
        'slug' => 'active-alpha',
        'code' => 'ACTIVE',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $alpha->forceFill(['created_at' => now()->subDay()])->saveQuietly();
    $beta->forceFill(['created_at' => now()->subDays(2)])->saveQuietly();
    $active->forceFill(['created_at' => now()->subDays(3)])->saveQuietly();

    $this->getJson('/api/v1/brands?is_active=false&search=a&sort_by=created_at&sort_dir=asc&per_page=15')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.id', $beta->id)
        ->assertJsonPath('data.1.id', $alpha->id);

    $this->getJson('/api/v1/brands?is_active=false&search=alpha&sort_by=created_at&sort_dir=desc&per_page=15')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $alpha->id);
});

it('generates unique brand slugs when names are duplicated', function () {
    $first = $this->postJson('/api/v1/brands', [
        'name' => 'Glow Lab',
        'code' => 'GLOW1',
    ])->assertCreated();

    $second = $this->postJson('/api/v1/brands', [
        'name' => 'Glow Lab',
        'code' => 'GLOW2',
    ])->assertCreated();

    $other = Brand::query()->create([
        'name' => 'Other Brand',
        'slug' => 'other-brand',
        'code' => 'OTHER',
    ]);

    $updated = $this->putJson("/api/v1/brands/{$other->id}", [
        'name' => 'Glow Lab',
    ])->assertOk();

    $first->assertJsonPath('data.slug', 'glow-lab');
    $second->assertJsonPath('data.slug', 'glow-lab-2');
    $updated->assertJsonPath('data.slug', 'glow-lab-3');

    expect(Brand::query()->pluck('slug')->all())
        ->toContain('glow-lab', 'glow-lab-2', 'glow-lab-3');
});
