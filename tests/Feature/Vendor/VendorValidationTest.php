<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

it('returns validation errors in production when creating a vendor without code', function () {
    config(['app.debug' => false]);

    Permission::firstOrCreate(['name' => 'Create_Vendor', 'guard_name' => 'sanctum']);

    $user = User::factory()->create();
    $user->givePermissionTo('Create_Vendor');
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/vendors', [
        'name' => 'Glamrush Vendor',
        'email' => 'vendor@example.com',
    ])
        ->assertUnprocessable()
        ->assertJsonPath('message', 'Validation failed')
        ->assertJsonValidationErrors('code');
});
