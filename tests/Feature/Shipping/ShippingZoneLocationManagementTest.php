<?php

use App\Models\User;
use Database\Seeders\ShippingZoneSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

function shippingZoneAdmin(array $permissions): User
{
    $user = User::factory()->create();
    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'sanctum']);
    }
    $user->givePermissionTo($permissions);
    Sanctum::actingAs($user);

    return $user;
}

it('creates and updates a zone containing grouped states and cities', function () {
    shippingZoneAdmin(['Create_Shipment', 'Update_Shipment', 'View_Shipment']);

    $created = $this->postJson('/api/v1/shipping/zones', [
        'name' => 'South West selected areas',
        'country' => 'NGA',
        'states' => ['OG'],
        'cities' => [
            ['state' => 'LA', 'city' => 'Ikeja'],
            ['state' => 'LA', 'city' => 'Surulere'],
        ],
        'is_active' => true,
    ])->assertCreated()
        ->assertJsonPath('data.states.0', 'OG')
        ->assertJsonPath('data.cities.0.state', 'LA')
        ->assertJsonPath('data.state', null)
        ->assertJsonCount(3, 'data.locations');

    $id = $created->json('data.id');
    $this->assertDatabaseHas('shipping_zone_locations', [
        'shipping_zone_id' => $id,
        'scope' => 'city',
        'state' => 'LA',
        'city' => 'Ikeja',
    ]);

    $this->putJson("/api/v1/shipping/zones/{$id}", [
        'states' => ['LA', 'OG'],
        'cities' => [],
    ])->assertOk()
        ->assertJsonCount(2, 'data.states')
        ->assertJsonCount(0, 'data.cities')
        ->assertJsonCount(2, 'data.locations');

    $this->assertDatabaseMissing('shipping_zone_locations', [
        'shipping_zone_id' => $id,
        'scope' => 'city',
    ]);
});

it('accepts the legacy singular location payload', function () {
    shippingZoneAdmin(['Create_Shipment']);

    $this->postJson('/api/v1/shipping/zones', [
        'name' => 'Ikeja',
        'country' => 'NGA',
        'state' => 'LA',
        'city' => 'Ikeja',
    ])->assertCreated()
        ->assertJsonPath('data.cities.0.city', 'Ikeja');
});

it('rejects invalid and already assigned locations', function () {
    shippingZoneAdmin(['Create_Shipment']);

    $this->postJson('/api/v1/shipping/zones', [
        'name' => 'Invalid city',
        'country' => 'NGA',
        'cities' => [['state' => 'LA', 'city' => 'Not a Lagos city']],
    ])->assertUnprocessable()
        ->assertJsonValidationErrors('cities.0.city');

    $payload = [
        'country' => 'NGA',
        'states' => ['LA'],
        'cities' => [],
    ];

    $this->postJson('/api/v1/shipping/zones', ['name' => 'Lagos one', ...$payload])->assertCreated();
    $this->postJson('/api/v1/shipping/zones', ['name' => 'Lagos two', ...$payload])
        ->assertConflict()
        ->assertJsonPath('success', false);
});

it('seeds normalized locations for every default shipping zone', function () {
    $this->seed(ShippingZoneSeeder::class);

    $this->assertDatabaseCount('shipping_zones', 4);
    $this->assertDatabaseCount('shipping_zone_locations', 4);
    $this->assertDatabaseHas('shipping_zone_locations', [
        'scope' => 'city',
        'state' => 'RI',
        'city' => 'Port Harcourt',
    ]);
});
