<?php

use App\Models\AppLog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\DiscountCode;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

function discountAdmin(array $permissions): User
{
    $user = User::factory()->create();
    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'sanctum']);
    }
    $user->givePermissionTo($permissions);
    Sanctum::actingAs($user);

    return $user;
}

function discountPayload(array $overrides = []): array
{
    return array_replace([
        'code' => ' welcome10 ', 'name' => 'Welcome', 'type' => 'percentage', 'value' => '10.00',
        'currency' => null, 'maximum_discount_amount' => '5000.00', 'minimum_subtotal' => '20000.00',
        'starts_at' => null, 'ends_at' => now()->addMonth()->toISOString(), 'is_active' => true,
        'total_usage_limit' => 1000, 'per_customer_usage_limit' => 1, 'first_order_only' => true,
        'applies_to_sale_items' => false, 'applies_to_all_storefronts' => true, 'storefront_ids' => [], 'targets' => [],
    ], $overrides);
}

it('requires authentication and explicit permissions', function () {
    $this->getJson('/api/v1/discount-codes')->assertUnauthorized();
    Sanctum::actingAs(User::factory()->create());
    $this->getJson('/api/v1/discount-codes')->assertForbidden();
});

it('creates and normalizes percentage fixed and free shipping codes', function () {
    $admin = discountAdmin(['Create_Discount']);
    $this->postJson('/api/v1/discount-codes', discountPayload())->assertCreated()->assertJsonPath('data.code', 'WELCOME10')->assertJsonPath('data.state', 'active');
    $this->postJson('/api/v1/discount-codes', discountPayload(['code' => 'fixed_5k', 'type' => 'fixed_amount', 'value' => '5000', 'currency' => 'ngn', 'maximum_discount_amount' => null]))
        ->assertCreated()->assertJsonPath('data.currency', 'NGN');
    $this->postJson('/api/v1/discount-codes', discountPayload(['code' => 'SHIP-FREE', 'type' => 'free_shipping', 'value' => null, 'currency' => null, 'maximum_discount_amount' => null]))->assertCreated();
    expect(AppLog::where('event', 'DISCOUNT_CODE_CREATED')->where('actor_id', $admin->id)->count())->toBe(3);
});

it('enforces conditional values schedules uniqueness and limits', function () {
    discountAdmin(['Create_Discount']);
    $this->postJson('/api/v1/discount-codes', discountPayload())->assertCreated();
    $this->postJson('/api/v1/discount-codes', discountPayload())->assertUnprocessable()->assertJsonValidationErrors('code');
    $this->postJson('/api/v1/discount-codes', discountPayload(['code' => 'OVER', 'value' => 101]))->assertUnprocessable()->assertJsonValidationErrors('value');
    $this->postJson('/api/v1/discount-codes', discountPayload(['code' => 'FIXED', 'type' => 'fixed_amount', 'maximum_discount_amount' => null]))->assertUnprocessable()->assertJsonValidationErrors('currency');
    $this->postJson('/api/v1/discount-codes', discountPayload(['code' => 'SCHED', 'starts_at' => now()->addDay(), 'ends_at' => now()]))->assertUnprocessable()->assertJsonValidationErrors('ends_at');
    $this->postJson('/api/v1/discount-codes', discountPayload(['code' => 'LIMIT', 'total_usage_limit' => 1, 'per_customer_usage_limit' => 2]))->assertUnprocessable()->assertJsonValidationErrors('per_customer_usage_limit');
});

it('targets root storefronts and every allowlisted catalog type atomically', function () {
    discountAdmin(['Create_Discount']);
    $root = Category::factory()->create(['parent_id' => null, 'is_active' => true]);
    $child = Category::factory()->create(['parent_id' => $root->id]);
    $brand = Brand::factory()->create();
    $product = Product::factory()->create(['brand_id' => $brand->id]);
    $product->categories()->sync([$child->id => ['id' => (string) Str::ulid(), 'is_primary' => true, 'sequence' => 1]]);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id]);
    $collection = Collection::create(['name' => 'Edit', 'slug' => 'edit', 'is_active' => true]);
    $targets = [
        ['target_type' => 'product', 'target_id' => $product->id, 'mode' => 'include'],
        ['target_type' => 'product_variant', 'target_id' => $variant->id, 'mode' => 'include'],
        ['target_type' => 'category', 'target_id' => $child->id, 'mode' => 'include'],
        ['target_type' => 'brand', 'target_id' => $brand->id, 'mode' => 'exclude'],
        ['target_type' => 'collection', 'target_id' => $collection->id, 'mode' => 'include'],
    ];
    $response = $this->postJson('/api/v1/discount-codes', discountPayload(['applies_to_all_storefronts' => false, 'storefront_ids' => [$root->id], 'targets' => $targets]))->assertCreated()->assertJsonCount(5, 'data.targets');
    $this->assertDatabaseHas('discount_code_storefronts', ['discount_code_id' => $response->json('data.id'), 'category_id' => $root->id]);
    $this->postJson('/api/v1/discount-codes', discountPayload(['code' => 'CHILD', 'applies_to_all_storefronts' => false, 'storefront_ids' => [$child->id]]))->assertUnprocessable()->assertJsonValidationErrors('storefront_ids.0');
    $this->postJson('/api/v1/discount-codes', discountPayload(['code' => 'CONFLICT', 'targets' => [$targets[0], [...$targets[0], 'mode' => 'exclude']]]))->assertUnprocessable()->assertJsonValidationErrors('targets.1');
    $this->assertDatabaseMissing('discount_codes', ['code' => 'CONFLICT']);
});

it('lists searches filters sorts paginates and returns derived states', function () {
    discountAdmin(['View_Discount']);
    DiscountCode::create(['code' => 'DRAFT', 'name' => 'Draft', 'type' => 'percentage', 'value' => 10, 'is_active' => false]);
    DiscountCode::create(['code' => 'FUTURE', 'name' => 'Future', 'type' => 'percentage', 'value' => 10, 'is_active' => true, 'starts_at' => now()->addDay()]);
    DiscountCode::create(['code' => 'OLD', 'name' => 'Old', 'type' => 'percentage', 'value' => 10, 'is_active' => true, 'ends_at' => now()->subDay()]);
    $this->getJson('/api/v1/discount-codes?search=FUTURE&state=scheduled&sort=code&direction=asc&per_page=1')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.state', 'scheduled');
    $this->getJson('/api/v1/discount-codes?sort=deleted_at')->assertUnprocessable()->assertJsonValidationErrors('sort');

    expect(DiscountCode::where('code', 'DRAFT')->first()->state())->toBe('draft')
        ->and(DiscountCode::where('code', 'FUTURE')->first()->state())->toBe('scheduled')
        ->and(DiscountCode::where('code', 'OLD')->first()->state())->toBe('expired');

    $active = DiscountCode::create(['code' => 'ACTIVE', 'name' => 'Active', 'type' => 'percentage', 'value' => 10, 'is_active' => true]);
    $inactive = DiscountCode::create(['code' => 'INACTIVE', 'name' => 'Inactive', 'type' => 'percentage', 'value' => 10, 'is_active' => false, 'starts_at' => now()->subDay()]);
    expect($active->state())->toBe('active')->and($inactive->state())->toBe('inactive');
});

it('rolls back the code when relationship synchronization fails', function () {
    $admin = User::factory()->create();
    $product = Product::factory()->create();
    $target = ['target_type' => 'product', 'target_id' => $product->id, 'mode' => 'include'];
    $service = app(\App\Domain\Discount\Services\DiscountCodeService::class);

    expect(fn () => $service->create([
        'code' => 'ROLLBACK', 'name' => 'Rollback', 'type' => 'percentage', 'value' => 10,
        'is_active' => false, 'first_order_only' => false, 'applies_to_sale_items' => false,
        'applies_to_all_storefronts' => true, 'targets' => [$target, $target],
    ], $admin))->toThrow(QueryException::class);

    $this->assertDatabaseMissing('discount_codes', ['code' => 'ROLLBACK']);
    $this->assertDatabaseMissing('app_logs', ['event' => 'DISCOUNT_CODE_CREATED']);
});

it('requires explicit activation permission', function () {
    $code = DiscountCode::create(['code' => 'LOCKED', 'name' => 'Locked', 'type' => 'percentage', 'value' => 10, 'is_active' => false]);
    discountAdmin(['View_Discount']);
    $this->postJson("/api/v1/discount-codes/{$code->id}/activate")->assertForbidden();
});

it('updates activates deactivates duplicates and audits without internal fields', function () {
    discountAdmin(['Create_Discount', 'View_Discount', 'Update_Discount', 'Activate_Discount', 'Deactivate_Discount', 'Duplicate_Discount']);
    $id = $this->postJson('/api/v1/discount-codes', discountPayload(['is_active' => false]))->assertCreated()->json('data.id');
    $this->patchJson("/api/v1/discount-codes/$id", ['name' => 'Changed'])->assertOk()->assertJsonPath('data.name', 'Changed');
    $this->postJson("/api/v1/discount-codes/$id/activate")->assertOk()->assertJsonPath('data.is_active', true);
    $this->postJson("/api/v1/discount-codes/$id/deactivate")->assertOk()->assertJsonPath('data.is_active', false);
    $copy = $this->postJson("/api/v1/discount-codes/$id/duplicate", ['code' => 'WELCOME_COPY'])->assertCreated()->assertJsonPath('data.is_active', false);
    $copy->assertJsonMissingPath('data.deleted_at')->assertJsonMissingPath('data.created_by_admin_id');
    expect(AppLog::whereIn('event', ['DISCOUNT_CODE_UPDATED', 'DISCOUNT_CODE_ACTIVATED', 'DISCOUNT_CODE_DEACTIVATED', 'DISCOUNT_CODE_DUPLICATED'])->count())->toBe(4);
});



