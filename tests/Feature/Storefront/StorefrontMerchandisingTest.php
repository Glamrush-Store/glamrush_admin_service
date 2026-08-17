<?php

use App\Domain\Storefront\Enums\HomepageSectionType;
use App\Infrastructure\Cache\StorefrontHomepageCache;
use App\Models\Category;
use App\Models\Product;
use App\Models\StorefrontCampaign;
use App\Models\StorefrontHomepageSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

function storefrontAdmin(array $permissions): User
{
    $user = User::factory()->create();
    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'sanctum']);
    }
    $user->givePermissionTo($permissions);
    Sanctum::actingAs($user);

    return $user;
}

function fragrancesStorefront(): Category
{
    return Category::factory()->create(['name' => 'Fragrances', 'slug' => 'fragrances', 'parent_id' => null]);
}

function merchandisingProduct(string $name): Product
{
    return Product::create([
        'name' => $name,
        'slug' => str($name)->slug().'-'.str()->random(5),
        'sequence' => str()->random(10),
        'type' => 'simple',
        'status' => 'published',
    ]);
}

it('requires admin authentication and campaign permissions', function () {
    fragrancesStorefront();

    $this->getJson('/api/v1/storefronts/fragrances/campaigns')->assertUnauthorized();

    Sanctum::actingAs(User::factory()->create());
    $this->getJson('/api/v1/storefronts/fragrances/campaigns')->assertForbidden();
});

it('allows an administrator to configure storefront announcement text', function () {
    $storefront = fragrancesStorefront();
    storefrontAdmin(['Update_Category', 'View_Category']);

    $this->putJson("/api/v1/categories/{$storefront->id}", [
        'announcement_primary_text' => 'Free delivery today',
        'announcement_secondary_text' => 'Book a private scent consultation',
    ])->assertOk()
        ->assertJsonPath('data.announcement_primary_text', 'Free delivery today')
        ->assertJsonPath('data.announcement_secondary_text', 'Book a private scent consultation');

    $this->getJson("/api/v1/categories/{$storefront->id}")
        ->assertOk()
        ->assertJsonPath('data.announcement_primary_text', 'Free delivery today')
        ->assertJsonPath('data.announcement_secondary_text', 'Book a private scent consultation');

    $this->putJson("/api/v1/categories/{$storefront->id}", [
        'announcement_primary_text' => '',
        'announcement_secondary_text' => '',
    ])->assertOk()
        ->assertJsonPath('data.announcement_primary_text', null)
        ->assertJsonPath('data.announcement_secondary_text', null);

    $this->assertDatabaseHas('categories', [
        'id' => $storefront->id,
        'announcement_primary_text' => null,
        'announcement_secondary_text' => null,
    ]);

    $this->putJson("/api/v1/categories/{$storefront->id}", [
        'announcement_secondary_text' => str_repeat('x', 161),
    ])->assertUnprocessable()
        ->assertJsonValidationErrors('announcement_secondary_text');
});

it('creates updates enables and deletes a campaign', function () {
    fragrancesStorefront();
    storefrontAdmin(['Create_StorefrontCampaign', 'View_StorefrontCampaign', 'Update_StorefrontCampaign', 'Delete_StorefrontCampaign']);

    $created = $this->postJson('/api/v1/storefronts/fragrances/campaigns', [
        'internal_name' => 'Midnight Edit',
        'title' => 'Leave a trace.',
        'priority' => 10,
        'starts_at' => now()->subHour()->toISOString(),
        'ends_at' => now()->addDay()->toISOString(),
    ])->assertCreated()->assertJsonPath('data.is_active', false);

    $id = $created->json('data.id');
    $this->putJson("/api/v1/storefronts/fragrances/campaigns/{$id}", ['title' => 'Leave your trace.'])
        ->assertOk()->assertJsonPath('data.title', 'Leave your trace.');
    $this->patchJson("/api/v1/storefronts/fragrances/campaigns/{$id}/enable")
        ->assertOk()->assertJsonPath('data.is_active', true);
    $this->deleteJson("/api/v1/storefronts/fragrances/campaigns/{$id}")->assertOk();
    $this->assertDatabaseMissing('storefront_campaigns', ['id' => $id]);
});

it('rejects invalid schedules and type-specific or unknown configuration', function () {
    fragrancesStorefront();
    storefrontAdmin(['Create_StorefrontCampaign', 'Create_StorefrontHomepageSection']);

    $this->postJson('/api/v1/storefronts/fragrances/campaigns', [
        'internal_name' => 'Bad schedule',
        'title' => 'Bad',
        'starts_at' => now()->addDay()->toISOString(),
        'ends_at' => now()->toISOString(),
    ])->assertUnprocessable()->assertJsonValidationErrors('ends_at');

    $this->postJson('/api/v1/storefronts/fragrances/homepage-sections', [
        'type' => 'category_products',
        'title' => 'Missing category',
        'config' => ['limit' => 100, 'surprise' => true],
    ])->assertUnprocessable()->assertJsonValidationErrors(['config.category_slug', 'config.limit', 'config']);
});

it('preserves manual product ordering and reorders sections', function () {
    fragrancesStorefront();
    storefrontAdmin(['Create_StorefrontHomepageSection', 'Update_StorefrontHomepageSection', 'View_StorefrontHomepageSection', 'Delete_StorefrontHomepageSection']);
    $firstProduct = merchandisingProduct('Amber');
    $secondProduct = merchandisingProduct('Musk');

    $manual = $this->postJson('/api/v1/storefronts/fragrances/homepage-sections', [
        'type' => 'manual_products',
        'title' => 'Selected for you',
        'config' => ['product_ids' => [$secondProduct->id, $firstProduct->id], 'limit' => 2],
        'display_order' => 9,
    ])->assertCreated()->assertJsonPath('data.config.product_ids.0', $secondProduct->id);

    $newest = $this->postJson('/api/v1/storefronts/fragrances/homepage-sections', [
        'type' => 'newest_products',
        'title' => 'New arrivals',
        'config' => ['limit' => 8],
        'display_order' => 2,
    ])->assertCreated();

    $this->putJson('/api/v1/storefronts/fragrances/homepage-sections/reorder', [
        'section_ids' => [$manual->json('data.id'), $newest->json('data.id')],
    ])->assertOk()
        ->assertJsonPath('data.0.display_order', 1)
        ->assertJsonPath('data.1.display_order', 2);

    $manualId = $manual->json('data.id');
    $this->putJson("/api/v1/storefronts/fragrances/homepage-sections/{$manualId}", ['title' => 'Hand-picked'])
        ->assertOk()
        ->assertJsonPath('data.title', 'Hand-picked')
        ->assertJsonPath('data.config.product_ids.0', $secondProduct->id);
    $this->patchJson("/api/v1/storefronts/fragrances/homepage-sections/{$manualId}/enable")
        ->assertOk()->assertJsonPath('data.is_active', true);

    expect(StorefrontHomepageSection::find($manualId)->products->pluck('id')->all())
        ->toBe([$secondProduct->id, $firstProduct->id]);

    $newestId = $newest->json('data.id');
    $this->deleteJson("/api/v1/storefronts/fragrances/homepage-sections/{$newestId}")->assertOk();
    $this->assertDatabaseMissing('storefront_homepage_sections', ['id' => $newestId]);
});

it('protects the internal endpoint and publishes only current ordered content', function () {
    fragrancesStorefront();
    config(['services.storefront_internal.token' => 'test-internal-secret']);
    $now = now();

    StorefrontCampaign::create(['storefront_slug' => 'fragrances', 'internal_name' => 'Low', 'title' => 'Low', 'priority' => 1, 'is_active' => true]);
    $winning = StorefrontCampaign::create(['storefront_slug' => 'fragrances', 'internal_name' => 'Winner', 'title' => 'Winner', 'priority' => 20, 'is_active' => true]);
    StorefrontCampaign::create(['storefront_slug' => 'fragrances', 'internal_name' => 'Future', 'title' => 'Future', 'priority' => 100, 'is_active' => true, 'starts_at' => $now->copy()->addHour()]);
    StorefrontCampaign::create(['storefront_slug' => 'fragrances', 'internal_name' => 'Expired', 'title' => 'Expired', 'priority' => 100, 'is_active' => true, 'ends_at' => $now->copy()->subSecond()]);

    $second = StorefrontHomepageSection::create(['storefront_slug' => 'fragrances', 'type' => HomepageSectionType::NewestProducts, 'title' => 'Second', 'config' => ['limit' => 8], 'display_order' => 2, 'is_active' => true]);
    $first = StorefrontHomepageSection::create(['storefront_slug' => 'fragrances', 'type' => HomepageSectionType::SaleProducts, 'title' => 'First', 'config' => ['limit' => 8], 'display_order' => 1, 'is_active' => true]);
    $categories = StorefrontHomepageSection::create(['storefront_slug' => 'fragrances', 'type' => HomepageSectionType::RandomCategories, 'title' => 'Categories', 'config' => ['limit' => 8], 'display_order' => 9, 'is_active' => true]);
    StorefrontHomepageSection::create(['storefront_slug' => 'fragrances', 'type' => HomepageSectionType::NewestProducts, 'title' => 'Draft', 'config' => [], 'display_order' => 0, 'is_active' => false]);
    StorefrontHomepageSection::create(['storefront_slug' => 'fragrances', 'type' => HomepageSectionType::NewestProducts, 'title' => 'Future', 'config' => [], 'display_order' => 0, 'is_active' => true, 'starts_at' => $now->copy()->addMinute()]);
    StorefrontHomepageSection::create(['storefront_slug' => 'fragrances', 'type' => HomepageSectionType::NewestProducts, 'title' => 'Expired', 'config' => [], 'display_order' => 0, 'is_active' => true, 'ends_at' => $now->copy()->subSecond()]);

    $this->getJson('/api/internal/v1/storefronts/fragrances/homepage')->assertUnauthorized();
    $this->withToken('wrong')->getJson('/api/internal/v1/storefronts/fragrances/homepage')->assertUnauthorized();
    $this->withToken('test-internal-secret')->getJson('/api/internal/v1/storefronts/fragrances/homepage')
        ->assertOk()
        ->assertJsonPath('data.campaign.id', $winning->id)
        ->assertJsonPath('data.sections.0.id', $categories->id)
        ->assertJsonPath('data.sections.1.id', $first->id)
        ->assertJsonPath('data.sections.2.id', $second->id)
        ->assertJsonCount(3, 'data.sections')
        ->assertJsonMissingPath('data.campaign.priority');
});

it('invalidates published cache when merchandising changes', function () {
    Cache::put(StorefrontHomepageCache::key('fragrances'), ['stale' => true], 300);
    StorefrontCampaign::create(['storefront_slug' => 'fragrances', 'internal_name' => 'Campaign', 'title' => 'Title']);
    expect(Cache::has(StorefrontHomepageCache::key('fragrances')))->toBeFalse();

    Cache::put(StorefrontHomepageCache::key('fragrances'), ['stale' => true], 300);
    $section = StorefrontHomepageSection::create(['storefront_slug' => 'fragrances', 'type' => HomepageSectionType::NewestProducts, 'title' => 'New', 'config' => []]);
    expect(Cache::has(StorefrontHomepageCache::key('fragrances')))->toBeFalse();

    Cache::put(StorefrontHomepageCache::key('fragrances'), ['stale' => true], 300);
    $section->update(['title' => 'Changed']);
    expect(Cache::has(StorefrontHomepageCache::key('fragrances')))->toBeFalse();
});
