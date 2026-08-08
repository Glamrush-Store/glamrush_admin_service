<?php

use App\Domain\Content\Services\ContentPageService;
use App\Models\AppLog;
use App\Models\Category;
use App\Models\ContentPage;
use App\Models\Faq;
use App\Models\FaqCategory;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

function contentAdmin(array $permissions): User
{
    $user = User::factory()->create();
    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'sanctum']);
    }
    $user->givePermissionTo($permissions);
    Sanctum::actingAs($user);

    return $user;
}

function pagePayload(array $overrides = []): array
{
    return array_replace(['title' => 'About Glamrush', 'content' => '<h2>Our story</h2><p>Thoughtfully curated.</p>', 'page_type' => 'about', 'is_published' => false, 'published_at' => null, 'expires_at' => null, 'applies_to_all_storefronts' => true, 'storefront_ids' => [], 'display_order' => 10], $overrides);
}

function faqPayload(string $categoryId, array $overrides = []): array
{
    return array_replace(['faq_category_id' => $categoryId, 'question' => 'How long does delivery take?', 'answer' => '<p>Delivery depends on your location.</p>', 'display_order' => 10, 'is_published' => false, 'published_at' => null, 'expires_at' => null, 'applies_to_all_storefronts' => true, 'storefront_ids' => []], $overrides);
}

it('requires authentication and content permissions', function () {
    $this->getJson('/api/v1/content-pages')->assertUnauthorized();
    Sanctum::actingAs(User::factory()->create());
    $this->getJson('/api/v1/content-pages')->assertForbidden();
});

it('creates every supported page type and generates normalized globally unique slugs', function (string $type) {
    contentAdmin(['Create_ContentPage']);
    $response = $this->postJson('/api/v1/content-pages', pagePayload(['title' => " $type Page ", 'page_type' => $type]))->assertCreated();
    expect($response->json('data.slug'))->toBe(str($type.' Page')->slug()->toString());
})->with(['about', 'contact', 'privacy_policy', 'terms', 'shipping_policy', 'returns_policy', 'custom']);

it('rejects duplicate slugs invalid schedules base64 and invalid storefronts', function () {
    contentAdmin(['Create_ContentPage']);
    $this->postJson('/api/v1/content-pages', pagePayload(['slug' => ' policy ', 'title' => 'First']))->assertCreated()->assertJsonPath('data.slug', 'policy');
    $this->postJson('/api/v1/content-pages', pagePayload(['slug' => 'policy', 'title' => 'Second']))->assertUnprocessable()->assertJsonValidationErrors('slug');
    $this->postJson('/api/v1/content-pages', pagePayload(['title' => 'Schedule', 'published_at' => now()->addDay(), 'expires_at' => now()]))->assertUnprocessable()->assertJsonValidationErrors('expires_at');
    $this->postJson('/api/v1/content-pages', pagePayload(['title' => 'Embedded', 'content' => '<img src="data:image/png;base64,x">']))->assertUnprocessable()->assertJsonValidationErrors('content');
    $root = Category::factory()->create(['parent_id' => null, 'is_active' => false]);
    $this->postJson('/api/v1/content-pages', pagePayload(['title' => 'Bad Store', 'applies_to_all_storefronts' => false, 'storefront_ids' => [$root->id]]))->assertUnprocessable()->assertJsonValidationErrors('storefront_ids.0');
    $activeRoot = Category::factory()->create(['parent_id' => null, 'is_active' => true]);
    $child = Category::factory()->create(['parent_id' => $activeRoot->id, 'is_active' => true]);
    $this->postJson('/api/v1/content-pages', pagePayload(['title' => 'Child Store', 'applies_to_all_storefronts' => false, 'storefront_ids' => [$child->id]]))->assertUnprocessable()->assertJsonValidationErrors('storefront_ids.0');
    $activeRoot->delete();
    $this->postJson('/api/v1/content-pages', pagePayload(['title' => 'Deleted Store', 'applies_to_all_storefronts' => false, 'storefront_ids' => [$activeRoot->id]]))->assertUnprocessable()->assertJsonValidationErrors('storefront_ids.0');
});

it('sanitizes unsafe html and validates contact settings', function () {
    contentAdmin(['Create_ContentPage']);
    $content = '<div><p onclick="steal()">Safe<script>alert(1)</script><a href="javascript:alert(1)" target="_blank">link</a></p></div>';
    $response = $this->postJson('/api/v1/content-pages', pagePayload(['title' => 'Safe Page', 'content' => $content]))->assertCreated();
    expect($response->json('data.content'))->toContain('<p>Safe')->not->toContain('script')->not->toContain('onclick')->not->toContain('javascript:');
    $this->postJson('/api/v1/content-pages', pagePayload(['title' => 'Bad Settings', 'settings' => ['email' => 'support@example.com']]))->assertUnprocessable()->assertJsonValidationErrors('settings');
    $this->postJson('/api/v1/content-pages', pagePayload(['title' => 'Contact', 'page_type' => 'contact', 'settings' => ['email' => 'bad', 'map_url' => 'javascript:alert(1)']]))->assertUnprocessable()->assertJsonValidationErrors(['settings.email', 'settings.map_url']);
    $this->postJson('/api/v1/content-pages', pagePayload(['title' => 'Secret', 'page_type' => 'contact', 'settings' => ['smtp_password' => 'secret']]))->assertUnprocessable()->assertJsonValidationErrors('settings');
    $this->postJson('/api/v1/content-pages', pagePayload(['title' => 'Only Script', 'content' => '<script>alert(1)</script>']))->assertUnprocessable()->assertJsonValidationErrors('content');
    $this->postJson('/api/v1/content-pages', pagePayload(['title' => 'Contact Us', 'page_type' => 'contact', 'settings' => ['email' => 'support@glamrush.test', 'phone' => '+2348000000000', 'map_url' => 'https://maps.example.test/location', 'social_links' => [['platform' => 'instagram', 'url' => 'https://instagram.com/glamrush']]]]))->assertCreated()->assertJsonPath('data.settings.email', 'support@glamrush.test');
});

it('supports storefront targeting publication states filters duplication and soft deletion', function () {
    contentAdmin(['Create_ContentPage', 'View_ContentPage', 'Update_ContentPage', 'Publish_ContentPage', 'Unpublish_ContentPage', 'Duplicate_ContentPage', 'Delete_ContentPage']);
    $root = Category::factory()->create(['parent_id' => null, 'is_active' => true]);
    $created = $this->postJson('/api/v1/content-pages', pagePayload(['title' => 'Store Policy', 'applies_to_all_storefronts' => false, 'storefront_ids' => [$root->id]]))->assertCreated()->assertJsonPath('data.state', 'draft');
    $id = $created->json('data.id');
    $this->postJson("/api/v1/content-pages/$id/publish")->assertOk()->assertJsonPath('data.state', 'published');
    $this->postJson("/api/v1/content-pages/$id/unpublish")->assertOk()->assertJsonPath('data.state', 'unpublished');
    $this->postJson("/api/v1/content-pages/$id/duplicate", ['slug' => 'store-policy-copy'])->assertCreated()->assertJsonPath('data.state', 'draft');
    $scheduled = ContentPage::create(['slug' => 'scheduled', 'title' => 'Scheduled', 'content' => '<p>x</p>', 'page_type' => 'custom', 'is_published' => true, 'published_at' => now()->addDay()]);
    $expired = ContentPage::create(['slug' => 'expired', 'title' => 'Expired', 'content' => '<p>x</p>', 'page_type' => 'custom', 'is_published' => true, 'expires_at' => now()->subDay()]);
    expect($scheduled->state())->toBe('scheduled')->and($expired->state())->toBe('expired');
    $this->getJson('/api/v1/content-pages?search=Scheduled&state=scheduled&storefront_id='.$root->id.'&sort=title&direction=asc&per_page=1')->assertOk()->assertJsonPath('data.0.state', 'scheduled');
    $this->getJson('/api/v1/content-pages?sort=deleted_at')->assertUnprocessable()->assertJsonValidationErrors('sort');
    $this->deleteJson("/api/v1/content-pages/$id")->assertOk();
    $this->assertSoftDeleted('content_pages', ['id' => $id]);
    expect(AppLog::where('event', 'CONTENT_PAGE_CREATED')->exists())->toBeTrue();
});

it('rolls back page creation when storefront synchronization fails', function () {
    $admin = User::factory()->create();
    expect(fn () => app(ContentPageService::class)->create(pagePayload(['slug' => 'rollback', 'applies_to_all_storefronts' => false, 'storefront_ids' => ['01INVALID00000000000000000']]), $admin))->toThrow(QueryException::class);
    $this->assertDatabaseMissing('content_pages', ['slug' => 'rollback']);
});

it('manages FAQ categories and deterministic ordering', function () {
    contentAdmin(['View_FaqCategory', 'Create_FaqCategory', 'Update_FaqCategory', 'Reorder_FaqCategory', 'Delete_FaqCategory']);
    $a = $this->postJson('/api/v1/faq-categories', ['name' => ' Shipping ', 'display_order' => 8])->assertCreated()->assertJsonPath('data.slug', 'shipping')->json('data.id');
    $b = $this->postJson('/api/v1/faq-categories', ['name' => 'Orders', 'display_order' => 2])->assertCreated()->json('data.id');
    $this->postJson('/api/v1/faq-categories/reorder', ['ids' => [$a, $b]])->assertOk();
    $this->getJson('/api/v1/faq-categories')->assertOk()->assertJsonPath('data.0.id', $a)->assertJsonPath('data.1.id', $b);
    $this->getJson('/api/v1/faq-categories?per_page=101')->assertUnprocessable()->assertJsonValidationErrors('per_page');
    $this->patchJson("/api/v1/faq-categories/$a", ['description' => 'Delivery questions'])->assertOk();
    $this->deleteJson("/api/v1/faq-categories/$b")->assertOk();
    $this->assertSoftDeleted('faq_categories', ['id' => $b]);
});

it('creates targets filters reorders publishes duplicates and deletes FAQs', function () {
    contentAdmin(['View_Faq', 'Create_Faq', 'Update_Faq', 'Publish_Faq', 'Unpublish_Faq', 'Duplicate_Faq', 'Reorder_Faq', 'Delete_Faq']);
    $category = FaqCategory::create(['name' => 'Shipping', 'slug' => 'shipping', 'is_active' => true]);
    $root = Category::factory()->create(['parent_id' => null, 'is_active' => true]);
    $first = $this->postJson('/api/v1/faqs', faqPayload($category->id, ['applies_to_all_storefronts' => false, 'storefront_ids' => [$root->id], 'answer' => '<p onclick="bad()">Two days<script>bad()</script></p>']))->assertCreated();
    $id = $first->json('data.id');
    expect($first->json('data.answer'))->not->toContain('onclick')->not->toContain('script');
    $second = $this->postJson('/api/v1/faqs', faqPayload($category->id, ['question' => 'Can I track it?']))->assertCreated()->json('data.id');
    $this->postJson('/api/v1/faqs/reorder', ['ids' => [$second, $id]])->assertOk();
    $this->getJson('/api/v1/faqs?faq_category_id='.$category->id.'&storefront_id='.$root->id.'&search=track&sort=display_order&direction=asc')->assertOk()->assertJsonCount(1, 'data');
    $this->postJson("/api/v1/faqs/$id/publish")->assertOk()->assertJsonPath('data.state', 'published');
    $this->postJson("/api/v1/faqs/$id/unpublish")->assertOk()->assertJsonPath('data.state', 'unpublished');
    $this->postJson("/api/v1/faqs/$id/duplicate")->assertCreated()->assertJsonPath('data.state', 'draft');
    $this->deleteJson("/api/v1/faqs/$id")->assertOk();
    $this->assertSoftDeleted('faqs', ['id' => $id]);
    expect(AppLog::where('event', 'FAQ_CREATED')->count())->toBe(2);
});

it('requires explicit publication permissions and excludes internal fields', function () {
    $category = FaqCategory::create(['name' => 'Orders', 'slug' => 'orders']);
    $faq = Faq::create(['faq_category_id' => $category->id, 'question' => 'Question?', 'answer' => '<p>Answer</p>']);
    contentAdmin(['View_Faq']);
    $this->postJson("/api/v1/faqs/{$faq->id}/publish")->assertForbidden();
    $this->getJson("/api/v1/faqs/{$faq->id}")->assertOk()->assertJsonMissingPath('data.created_by_admin_id')->assertJsonMissingPath('data.deleted_at');
});
