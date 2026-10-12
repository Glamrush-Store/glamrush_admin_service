<?php

use App\Domain\Storefront\Enums\HomepageSectionType;
use App\Infrastructure\Cache\StorefrontHomepageCache;
use App\Models\Category;
use App\Models\Product;
use App\Models\StorefrontCampaign;
use App\Models\StorefrontHomepageSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
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

it('creates a split image banner with sorted sides and optional ctas', function () {
    Storage::fake(config('media-library.disk_name'));
    fragrancesStorefront();
    storefrontAdmin(['Create_StorefrontHomepageSection', 'View_StorefrontHomepageSection', 'Update_StorefrontHomepageSection']);

    $created = $this->post('/api/v1/storefronts/fragrances/homepage-sections', [
        'type' => 'split_image_banner',
        'title' => 'Fragrance families',
        'config' => [
            'items' => [
                [
                    'slot' => 'right',
                    'description' => 'Warm scents for evenings.',
                    'cta_label' => null,
                    'cta_url' => null,
                    'sort_order' => 20,
                ],
                [
                    'slot' => 'left',
                    'description' => 'Fresh scents for every day.',
                    'cta_label' => 'Shop fresh',
                    'cta_url' => '/collections/fresh',
                    'sort_order' => 10,
                ],
            ],
        ],
        'left_image' => UploadedFile::fake()->image('left.jpg', 1200, 800),
        'right_image' => UploadedFile::fake()->image('right.jpg', 1200, 800),
        'mobile_image' => UploadedFile::fake()->image('mobile.jpg', 800, 1000),
        'display_order' => 4,
        'is_active' => true,
    ], ['Accept' => 'application/json'])
        ->assertCreated()
        ->assertJsonPath('data.type', 'split_image_banner')
        ->assertJsonPath('data.config.items.0.slot', 'left')
        ->assertJsonPath('data.config.items.1.slot', 'right');

    foreach (['left', 'right', 'mobile'] as $key) {
        expect($created->json("data.images.{$key}"))->not->toBeEmpty();
    }

    $id = $created->json('data.id');
    $this->post("/api/v1/storefronts/fragrances/homepage-sections/{$id}", [
        '_method' => 'PUT',
        'config' => [
            'items' => [
                ['slot' => 'right', 'description' => 'Updated right.', 'sort_order' => 2],
                ['slot' => 'left', 'description' => 'Updated left.', 'sort_order' => 1],
            ],
        ],
    ], ['Accept' => 'application/json'])
        ->assertOk()
        ->assertJsonPath('data.config.items.0.slot', 'left')
        ->assertJsonPath('data.config.items.0.description', 'Updated left.')
        ->assertJsonPath('data.images.mobile', $created->json('data.images.mobile'));

    config(['services.storefront_internal.token' => 'test-internal-secret']);
    $this->withToken('test-internal-secret')
        ->getJson('/api/internal/v1/storefronts/fragrances/homepage')
        ->assertOk()
        ->assertJsonPath('data.sections.0.id', $id)
        ->assertJsonPath('data.sections.0.config.items.0.slot', 'left')
        ->assertJsonPath('data.sections.0.images.mobile', $created->json('data.images.mobile'));
});

it('validates split image banner images sides and optional cta pairs', function () {
    fragrancesStorefront();
    storefrontAdmin(['Create_StorefrontHomepageSection']);

    $this->postJson('/api/v1/storefronts/fragrances/homepage-sections', [
        'type' => 'split_image_banner',
        'title' => 'Invalid split',
        'config' => [
            'items' => [
                ['slot' => 'left', 'description' => 'Left', 'cta_label' => 'Browse', 'sort_order' => 2],
                ['slot' => 'left', 'description' => 'Duplicate', 'sort_order' => 1],
            ],
        ],
    ])->assertUnprocessable()
        ->assertJsonValidationErrors([
            'left_image',
            'right_image',
            'mobile_image',
            'config.items',
            'config.items.0.cta_url',
        ]);
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
        ->assertJsonPath('data.sections.0.id', $first->id)
        ->assertJsonPath('data.sections.1.id', $second->id)
        ->assertJsonPath('data.sections.2.id', $categories->id)
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
