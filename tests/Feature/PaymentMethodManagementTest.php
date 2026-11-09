<?php

use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

it('creates the Bachs payment method using its storefront provider code', function () {
    Permission::findOrCreate('Create_PaymentMethod', 'sanctum');

    $user = User::factory()->create();
    $user->givePermissionTo('Create_PaymentMethod');
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/payment-methods', [
        'name' => 'Bachs',
        'code' => ' BACHS ',
        'description' => 'Pay securely through Bachs.',
        'is_active' => true,
        'sort_order' => 3,
        'public_config' => [
            'requires_redirect' => true,
            'payment_flow' => 'redirect',
            'supports_verification' => true,
        ],
    ])->assertCreated()
        ->assertJsonPath('data.code', 'bachs')
        ->assertJsonPath('data.is_active', true);

    expect(PaymentMethod::query()->where('code', 'bachs')->exists())->toBeTrue();
});
