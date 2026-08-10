<?php

use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingMethod;
use App\Models\ShippingRate;
use App\Models\ShippingZone;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    Schema::table('product_variants', function (Blueprint $table) {
        $table->unsignedInteger('reserved_quantity')->default(0);
    });

    Schema::create('customer_accounts', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->string('phone')->nullable();
        $table->string('password');
        $table->boolean('is_active')->default(true);
        $table->timestamps();
        $table->softDeletes();
    });

    Schema::create('orders', function (Blueprint $table) {
        $table->ulid('id')->primary();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->string('guest_id')->nullable();
        $table->string('idempotency_owner');
        $table->string('idempotency_key');
        $table->string('idempotency_request_hash', 64);
        $table->string('order_number')->unique();
        $table->string('status');
        $table->ulid('discount_code_id')->nullable();
        $table->string('discount_code')->nullable();
        $table->decimal('subtotal', 12, 2);
        $table->decimal('discount_amount', 12, 2)->default(0);
        $table->decimal('shipping_amount', 12, 2)->default(0);
        $table->decimal('shipping_discount_amount', 12, 2)->default(0);
        $table->json('discount_snapshot')->nullable();
        $table->decimal('total', 12, 2);
        $table->string('currency', 3);
        $table->ulid('shipping_rate_id')->nullable();
        $table->string('shipping_method_name');
        $table->string('shipping_zone_name');
        $table->json('shipping_address');
        $table->json('billing_address')->nullable();
        $table->timestamp('placed_at')->nullable();
        $table->timestamp('paid_at')->nullable();
        $table->timestamp('inventory_committed_at')->nullable();
        $table->timestamp('expires_at')->nullable();
        $table->timestamp('cancelled_at')->nullable();
        $table->timestamps();
        $table->unique(['idempotency_owner', 'idempotency_key']);
    });

    Schema::create('order_items', function (Blueprint $table) {
        $table->ulid('id')->primary();
        $table->ulid('order_id');
        $table->ulid('product_id');
        $table->ulid('product_variant_id');
        $table->string('product_name');
        $table->string('product_slug');
        $table->string('sku');
        $table->decimal('unit_price', 12, 2);
        $table->unsignedSmallInteger('quantity');
        $table->decimal('line_subtotal', 12, 2);
        $table->decimal('discount_amount', 12, 2)->default(0);
        $table->decimal('line_total', 12, 2);
        $table->json('product_snapshot')->nullable();
        $table->timestamps();
    });

    Schema::create('payments', function (Blueprint $table) {
        $table->ulid('id')->primary();
        $table->ulid('order_id');
        $table->string('idempotency_owner');
        $table->string('idempotency_key');
        $table->string('idempotency_request_hash', 64);
        $table->ulid('payment_method_id')->nullable();
        $table->string('provider');
        $table->string('reference')->unique();
        $table->string('provider_reference')->nullable();
        $table->string('transaction_id')->nullable();
        $table->decimal('amount', 12, 2);
        $table->string('currency', 3);
        $table->string('status');
        $table->text('authorization_url')->nullable();
        $table->timestamp('paid_at')->nullable();
        $table->timestamp('failed_at')->nullable();
        $table->json('metadata')->nullable();
        $table->timestamps();
    });

    Schema::create('payment_transactions', function (Blueprint $table) {
        $table->ulid('id')->primary();
        $table->ulid('payment_id');
        $table->string('event_key')->unique();
        $table->string('type');
        $table->string('status')->nullable();
        $table->string('provider_reference')->nullable();
        $table->decimal('amount', 12, 2)->nullable();
        $table->string('currency', 3)->nullable();
        $table->json('payload')->nullable();
        $table->timestamps();
    });
});

function manualOrderAdmin(): User
{
    $user = User::factory()->create();
    Permission::firstOrCreate(['name' => 'Create_Order', 'guard_name' => 'sanctum']);
    $user->givePermissionTo('Create_Order');
    Sanctum::actingAs($user);

    return $user;
}

function manualOrderFixtures(): array
{
    $product = Product::create([
        'name' => 'Amber Perfume', 'slug' => 'amber-perfume', 'sequence' => 'MANUAL-1',
        'type' => 'variable', 'status' => 'published',
    ]);
    $variant = ProductVariant::create([
        'product_id' => $product->id, 'sku' => 'AMBER-50ML', 'price' => 15000,
        'manage_stock' => true, 'stock_quantity' => 10, 'reserved_quantity' => 3,
        'in_stock' => true, 'attributes' => ['size' => '50ml'], 'status' => 'active',
    ]);
    $payment = PaymentMethod::create(['name' => 'POS', 'code' => 'pos', 'is_active' => true]);
    $method = ShippingMethod::create(['name' => 'Store Pickup', 'code' => 'pickup', 'is_active' => true]);
    $zone = ShippingZone::create(['name' => 'Lagos', 'country' => 'NG', 'is_active' => true]);
    $rate = ShippingRate::create([
        'shipping_zone_id' => $zone->id, 'shipping_method_id' => $method->id,
        'rate_type' => 'flat', 'amount' => 2500, 'is_active' => true,
    ]);

    return compact('variant', 'payment', 'method', 'zone', 'rate');
}

function manualOrderPayload(array $fixtures, array $overrides = []): array
{
    return array_replace_recursive([
        'items' => [['product_variant_id' => $fixtures['variant']->id, 'quantity' => 2, 'unit_price' => '12500.00']],
        'payment_method_id' => $fixtures['payment']->id,
        'transaction_reference' => 'POS-1001',
        'shipping_method_id' => $fixtures['method']->id,
        'shipping_zone_id' => $fixtures['zone']->id,
        'shipping_rate_id' => $fixtures['rate']->id,
        'shipping_address' => [
            'name' => 'Ada Okafor', 'email' => 'ada@example.com', 'phone' => '+2348000000000',
            'address_line_1' => '12 Admiralty Way', 'city' => 'Lekki', 'state' => 'Lagos', 'country' => 'NG',
        ],
    ], $overrides);
}

it('records a complete offline sale and deducts only unreserved stock', function () {
    $admin = manualOrderAdmin();
    $fixtures = manualOrderFixtures();

    $response = $this->withHeader('Idempotency-Key', 'offline-sale-1001')
        ->postJson('/api/v1/orders/manual', manualOrderPayload($fixtures))
        ->assertCreated()
        ->assertJsonPath('data.source', 'manual_admin')
        ->assertJsonPath('data.is_manual', true)
        ->assertJsonPath('data.order_info.subtotal', '25000.00')
        ->assertJsonPath('data.order_info.shipping_amount', '2500.00')
        ->assertJsonPath('data.order_info.total', '27500.00')
        ->assertJsonPath('data.payment_info.payment_method.code', 'pos')
        ->assertJsonPath('data.shipping_info.shipment.status', 'delivered');

    $orderId = $response->json('data.id');
    $this->assertDatabaseHas('orders', ['id' => $orderId, 'status' => 'completed']);
    $this->assertDatabaseHas('payments', ['order_id' => $orderId, 'status' => 'paid', 'amount' => 27500]);
    $this->assertDatabaseHas('payment_transactions', ['type' => 'capture', 'status' => 'paid']);
    $this->assertDatabaseHas('shipments', ['order_id' => $orderId, 'status' => 'delivered']);
    $this->assertDatabaseHas('product_variants', ['id' => $fixtures['variant']->id, 'stock_quantity' => 8, 'reserved_quantity' => 3]);
    $this->assertDatabaseHas('app_logs', ['event' => 'MANUAL_ORDER_CREATED', 'actor_id' => $admin->id]);
});

it('replays an identical idempotent request without double-counting stock', function () {
    manualOrderAdmin();
    $fixtures = manualOrderFixtures();
    $payload = manualOrderPayload($fixtures);

    $first = $this->withHeader('Idempotency-Key', 'offline-sale-1002')->postJson('/api/v1/orders/manual', $payload)->assertCreated();
    $second = $this->withHeader('Idempotency-Key', 'offline-sale-1002')->postJson('/api/v1/orders/manual', $payload)->assertOk();

    expect($second->json('data.id'))->toBe($first->json('data.id'));
    $this->assertDatabaseCount('orders', 1);
    $this->assertDatabaseHas('product_variants', ['id' => $fixtures['variant']->id, 'stock_quantity' => 8]);

    $payload['items'][0]['quantity'] = 3;
    $this->withHeader('Idempotency-Key', 'offline-sale-1002')->postJson('/api/v1/orders/manual', $payload)->assertConflict();
});

it('rejects unavailable stock and rolls the entire sale back', function () {
    manualOrderAdmin();
    $fixtures = manualOrderFixtures();
    $payload = manualOrderPayload($fixtures, ['items' => [[
        'product_variant_id' => $fixtures['variant']->id, 'quantity' => 8, 'unit_price' => '12500.00',
    ]]]);

    $this->withHeader('Idempotency-Key', 'offline-sale-1003')->postJson('/api/v1/orders/manual', $payload)
        ->assertConflict();

    $this->assertDatabaseCount('orders', 0);
    $this->assertDatabaseHas('product_variants', ['id' => $fixtures['variant']->id, 'stock_quantity' => 10]);
});
