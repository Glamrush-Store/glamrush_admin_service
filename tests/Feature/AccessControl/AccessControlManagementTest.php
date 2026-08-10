<?php

use App\Models\AppLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function accessControlPermission(string $name): Permission
{
    return Permission::firstOrCreate(['name' => $name, 'guard_name' => 'sanctum']);
}

function accessControlAdmin(array $permissions, bool $superAdmin = false): User
{
    $user = User::factory()->create();
    $permissionModels = collect($permissions)->map(fn (string $name) => accessControlPermission($name));

    if ($superAdmin) {
        $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'sanctum']);
        $role->syncPermissions($permissionModels);
        $user->assignRole($role);
    } else {
        $user->givePermissionTo($permissionModels);
    }

    Sanctum::actingAs($user);

    return $user;
}

it('requires authentication and the matching access-control permissions', function () {
    $this->getJson('/api/v1/roles')->assertUnauthorized();

    Sanctum::actingAs(User::factory()->create());
    $this->getJson('/api/v1/roles')->assertForbidden();
    $this->getJson('/api/v1/users')->assertForbidden();
});

it('creates lists shows updates and deletes roles with permissions', function () {
    $admin = accessControlAdmin(['ViewAny_Role', 'View_Role', 'Create_Role', 'Update_Role', 'Delete_Role']);
    accessControlPermission('View_Product');
    accessControlPermission('Update_Product');

    $created = $this->postJson('/api/v1/roles', [
        'name' => ' Catalog Manager ',
        'permissions' => ['View_Product'],
    ])->assertCreated()
        ->assertJsonPath('data.name', 'catalog_manager')
        ->assertJsonPath('data.permissions.0.name', 'View_Product');

    $roleId = $created->json('data.id');
    $this->getJson('/api/v1/roles?search=catalog&sort=name&direction=desc')
        ->assertOk()->assertJsonPath('data.0.id', $roleId);
    $this->getJson("/api/v1/roles/{$roleId}")
        ->assertOk()->assertJsonPath('data.permissions_count', 1);

    $this->putJson("/api/v1/roles/{$roleId}/permissions", [
        'permissions' => ['View_Product', 'Update_Product'],
    ])->assertOk()->assertJsonPath('data.permissions_count', 2);

    $this->patchJson("/api/v1/roles/{$roleId}", ['name' => 'Product Team'])
        ->assertOk()->assertJsonPath('data.name', 'product_team');

    $this->deleteJson("/api/v1/roles/{$roleId}")->assertOk();
    $this->assertDatabaseMissing('roles', ['id' => $roleId]);
    expect(AppLog::where('actor_id', $admin->id)->whereIn('event', ['ROLE_CREATED', 'ROLE_UPDATED', 'ROLE_DELETED'])->count())->toBe(4);
});

it('returns a permission catalog grouped by parsed action and resource fields', function () {
    accessControlAdmin(['ViewAny_Role']);
    accessControlPermission('Update_Product');

    $this->getJson('/api/v1/permissions')->assertOk()
        ->assertJsonFragment(['name' => 'Update_Product', 'action' => 'Update', 'resource' => 'Product']);
});

it('does not delete assigned or system roles', function () {
    accessControlAdmin(['Delete_Role', 'Update_Role'], true);
    $assigned = Role::create(['name' => 'sales_agent', 'guard_name' => 'sanctum']);
    User::factory()->create()->assignRole($assigned);

    $this->deleteJson("/api/v1/roles/{$assigned->id}")->assertConflict();

    $system = Role::where('name', 'super_admin')->firstOrFail();
    $this->patchJson("/api/v1/roles/{$system->id}", ['name' => 'owner'])->assertConflict();
    $this->deleteJson("/api/v1/roles/{$system->id}")->assertConflict();
});

it('creates updates filters and deletes users with one assigned role', function () {
    $admin = accessControlAdmin(['ViewAny_User', 'View_User', 'Create_User', 'Update_User', 'Delete_User']);
    $sales = Role::create(['name' => 'sales', 'guard_name' => 'sanctum']);
    $manager = Role::create(['name' => 'manager', 'guard_name' => 'sanctum']);

    $created = $this->postJson('/api/v1/users', [
        'name' => 'Ada Sales',
        'email' => ' ADA@EXAMPLE.COM ',
        'password' => 'Password123',
        'password_confirmation' => 'Password123',
        'role_id' => $sales->id,
    ])->assertCreated()
        ->assertJsonPath('data.email', 'ada@example.com')
        ->assertJsonPath('data.role.id', $sales->id)
        ->assertJsonMissingPath('data.password');

    $userId = $created->json('data.id');
    $this->getJson("/api/v1/users?role_id={$sales->id}&search=ada")
        ->assertOk()->assertJsonPath('data.0.id', $userId);
    $this->getJson("/api/v1/users/{$userId}")->assertOk()->assertJsonPath('data.role.name', 'sales');

    $this->patchJson("/api/v1/users/{$userId}", [
        'name' => 'Ada Manager',
        'role_id' => $manager->id,
        'password' => 'NewPassword456',
        'password_confirmation' => 'NewPassword456',
    ])->assertOk()->assertJsonPath('data.role.id', $manager->id);

    expect(User::findOrFail($userId)->roles()->count())->toBe(1);
    $this->deleteJson("/api/v1/users/{$userId}")->assertOk();
    $this->assertDatabaseMissing('users', ['id' => $userId]);
    expect(AppLog::where('actor_id', $admin->id)->whereIn('event', ['USER_CREATED', 'USER_UPDATED', 'USER_DELETED'])->count())->toBe(3);
});

it('protects self management and super administrator boundaries', function () {
    $permissions = ['Create_User', 'Update_User', 'Delete_User'];
    $superAdmin = accessControlAdmin($permissions, true);
    $normalRole = Role::create(['name' => 'normal_admin', 'guard_name' => 'sanctum']);

    $this->patchJson("/api/v1/users/{$superAdmin->id}", ['role_id' => $normalRole->id])->assertConflict();
    $this->deleteJson("/api/v1/users/{$superAdmin->id}")->assertConflict();

    $ordinaryAdmin = User::factory()->create();
    $ordinaryAdmin->givePermissionTo(collect($permissions)->map(fn (string $name) => accessControlPermission($name)));
    Sanctum::actingAs($ordinaryAdmin);

    $superRole = Role::where('name', 'super_admin')->firstOrFail();
    $this->postJson('/api/v1/users', [
        'name' => 'Escalated User', 'email' => 'escalated@example.com',
        'password' => 'Password123', 'password_confirmation' => 'Password123', 'role_id' => $superRole->id,
    ])->assertForbidden();
    $this->patchJson("/api/v1/users/{$superAdmin->id}", ['email' => 'taken-over@example.com'])->assertForbidden();
});
