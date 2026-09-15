<?php

use App\Models\AttributeType;
use App\Models\SkuAttributeCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

function attributeTypeAdmin(array $permissions): User
{
    $user = User::factory()->create();
    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'sanctum']);
    }
    $user->givePermissionTo($permissions);
    Sanctum::actingAs($user);

    return $user;
}

function attributeTypeRecord(string $value, string $category = 'Fragrance'): AttributeType
{
    return AttributeType::create([
        'category' => $category,
        'value' => $value,
        'label' => ucfirst($value),
        'display_type' => 'select',
    ]);
}

it('requires admin authentication and separate attribute-type permissions', function () {
    $this->getJson('/api/v1/attribute-types')->assertUnauthorized();

    Sanctum::actingAs(User::factory()->create());
    $this->getJson('/api/v1/attribute-types')->assertForbidden();
    $this->postJson('/api/v1/attribute-types', [
        'value' => 'shade', 'label' => 'Shade', 'display_type' => 'swatch',
    ])->assertForbidden();
});

it('checks show update and delete permissions independently', function () {
    $type = attributeTypeRecord('volume');
    attributeTypeAdmin(['ViewAny_AttributeType']);

    $this->getJson("/api/v1/attribute-types/{$type->id}")->assertForbidden();
    $this->patchJson("/api/v1/attribute-types/{$type->id}", ['label' => 'Bottle size'])->assertForbidden();
    $this->deleteJson("/api/v1/attribute-types/{$type->id}")->assertForbidden();
    $this->assertDatabaseHas('attribute_types', ['id' => $type->id, 'label' => 'Volume']);
});

it('creates shows updates and deletes an attribute type', function () {
    attributeTypeAdmin([
        'ViewAny_AttributeType', 'View_AttributeType', 'Create_AttributeType',
        'Update_AttributeType', 'Delete_AttributeType',
    ]);

    $created = $this->postJson('/api/v1/attribute-types', [
        'category' => 'Cosmetics', 'value' => 'shade', 'label' => 'Shade', 'display_type' => 'swatch',
    ])->assertCreated()->assertJsonPath('data.value', 'shade');

    $id = $created->json('data.id');
    $this->getJson("/api/v1/attribute-types/{$id}")->assertOk()->assertJsonPath('data.category', 'Cosmetics');
    $this->patchJson("/api/v1/attribute-types/{$id}", ['label' => 'Colour shade'])
        ->assertOk()->assertJsonPath('data.label', 'Colour shade');
    $this->deleteJson("/api/v1/attribute-types/{$id}")->assertOk();
    $this->assertDatabaseMissing('attribute_types', ['id' => $id]);
    $this->getJson("/api/v1/attribute-types/{$id}")->assertNotFound();
});

it('lists matching records with pagination and allowlisted sorting', function () {
    attributeTypeAdmin(['ViewAny_AttributeType']);
    attributeTypeRecord('volume');
    attributeTypeRecord('concentration');
    attributeTypeRecord('shade', 'Cosmetics');

    $this->getJson('/api/v1/attribute-types?search=vol&category=Fragrance&per_page=1&sort_by=value&sort_dir=asc')
        ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.value', 'volume')
        ->assertJsonPath('meta.per_page', 1);
    $this->getJson('/api/v1/attribute-types?category=Fragrance&sort_by=value&sort_dir=asc')
        ->assertOk()->assertJsonPath('data.0.value', 'concentration')
        ->assertJsonPath('data.1.value', 'volume');
    $this->getJson('/api/v1/attribute-types?sort_by=value%20desc&sort_dir=sideways&per_page=101')
        ->assertUnprocessable()->assertJsonValidationErrors(['sort_by', 'sort_dir', 'per_page']);
});

it('validates required fields duplicate values and safe attribute keys', function () {
    attributeTypeAdmin(['Create_AttributeType', 'Update_AttributeType']);
    $existing = attributeTypeRecord('volume');

    $this->postJson('/api/v1/attribute-types', ['value' => 'volume', 'label' => 'Duplicate', 'display_type' => 'select'])
        ->assertUnprocessable()->assertJsonValidationErrors('value');
    $this->postJson('/api/v1/attribute-types', ['value' => 'Invalid Value', 'label' => '', 'display_type' => ''])
        ->assertUnprocessable()->assertJsonValidationErrors(['value', 'label', 'display_type']);
    $this->putJson("/api/v1/attribute-types/{$existing->id}", ['value' => 'volume', 'label' => 'Volume label'])
        ->assertOk()->assertJsonPath('data.label', 'Volume label');
    $second = attributeTypeRecord('shade', 'Cosmetics');
    $this->patchJson("/api/v1/attribute-types/{$second->id}", ['value' => 'volume'])
        ->assertUnprocessable()->assertJsonValidationErrors('value');
});

it('does not orphan SKU attribute codes when deleting or renaming a type', function () {
    attributeTypeAdmin(['Delete_AttributeType', 'Update_AttributeType']);
    $type = attributeTypeRecord('volume');
    SkuAttributeCode::create([
        'type' => 'volume', 'value' => '50ml', 'code' => '50ML',
        'display_type' => 'select', 'meta' => [], 'is_active' => true,
    ]);

    $this->deleteJson("/api/v1/attribute-types/{$type->id}")->assertStatus(409);
    $this->patchJson("/api/v1/attribute-types/{$type->id}", ['value' => 'capacity'])->assertStatus(409);
    $this->patchJson("/api/v1/attribute-types/{$type->id}", ['label' => 'Bottle size'])
        ->assertOk()->assertJsonPath('data.label', 'Bottle size');
    $this->assertDatabaseHas('attribute_types', ['id' => $type->id, 'value' => 'volume']);
});
