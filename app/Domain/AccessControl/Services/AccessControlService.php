<?php

namespace App\Domain\AccessControl\Services;

use App\Domain\Shared\Actions\CreateAppLogAction;
use App\Exceptions\BusinessException;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class AccessControlService
{
    public function __construct(private CreateAppLogAction $log) {}

    public function createRole(array $data, User $actor): Role
    {
        if ($data['name'] === 'super_admin') {
            throw new BusinessException('The super_admin role name is reserved for the system role.', [], 409);
        }

        return DB::transaction(function () use ($data, $actor): Role {
            $role = Role::create(['name' => $data['name'], 'guard_name' => 'sanctum']);
            $role->syncPermissions($data['permissions'] ?? []);

            $this->audit('ROLE_CREATED', 'Role created', $role, $actor, ['name', 'permissions']);

            return $this->loadRole($role);
        });
    }

    public function updateRole(Role $role, array $data, User $actor): Role
    {
        $this->assertMutableRole($role);

        return DB::transaction(function () use ($role, $data, $actor): Role {
            $changed = [];

            if (array_key_exists('name', $data) && $data['name'] !== $role->name) {
                $role->update(['name' => $data['name']]);
                $changed[] = 'name';
            }

            if (array_key_exists('permissions', $data)) {
                $role->syncPermissions($data['permissions']);
                $changed[] = 'permissions';
            }

            $this->audit('ROLE_UPDATED', 'Role updated', $role, $actor, $changed);

            return $this->loadRole($role);
        });
    }

    public function syncRolePermissions(Role $role, array $permissions, User $actor): Role
    {
        return $this->updateRole($role, ['permissions' => $permissions], $actor);
    }

    public function deleteRole(Role $role, User $actor): void
    {
        $this->assertMutableRole($role);

        if ($role->users()->exists()) {
            throw new BusinessException('This role cannot be deleted while it is assigned to users.', [
                'users_count' => $role->users()->count(),
            ], 409);
        }

        DB::transaction(function () use ($role, $actor): void {
            $context = ['role_id' => $role->id, 'role_name' => $role->name, 'changed_fields' => ['deleted']];
            $role->delete();
            $this->log->run('info', 'ROLE_DELETED', 'Role deleted', $context, $actor);
        });
    }

    public function createUser(array $data, User $actor): User
    {
        return DB::transaction(function () use ($data, $actor): User {
            $role = Role::query()->where('guard_name', 'sanctum')->findOrFail($data['role_id']);
            $this->assertMayAssignRole($role, $actor);

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);
            $user->syncRoles([$role]);

            $this->auditUser('USER_CREATED', 'Admin user created', $user, $actor, ['name', 'email', 'role']);

            return $this->loadUser($user);
        });
    }

    public function updateUser(User $user, array $data, User $actor): User
    {
        if ($user->hasRole('super_admin') && ! $actor->hasRole('super_admin')) {
            throw new BusinessException('Only a super administrator can update a super administrator account.', [], 403);
        }

        return DB::transaction(function () use ($user, $data, $actor): User {
            $changed = [];

            foreach (['name', 'email', 'password'] as $field) {
                if (array_key_exists($field, $data)) {
                    $user->{$field} = $data[$field];
                    $changed[] = $field;
                }
            }

            if ($user->isDirty()) {
                $user->save();
            }

            if (array_key_exists('role_id', $data)) {
                $role = Role::query()->where('guard_name', 'sanctum')->findOrFail($data['role_id']);
                $currentRole = $user->roles()->first();

                if ($actor->is($user) && $currentRole?->id !== $role->id) {
                    throw new BusinessException('You cannot change your own role.', [], 409);
                }

                $this->assertMayAssignRole($role, $actor);
                $this->assertNotRemovingLastSuperAdmin($user, $role);
                $user->syncRoles([$role]);
                $changed[] = 'role';
            }

            $this->auditUser('USER_UPDATED', 'Admin user updated', $user, $actor, array_values(array_unique($changed)));

            return $this->loadUser($user);
        });
    }

    public function deleteUser(User $user, User $actor): void
    {
        if ($actor->is($user)) {
            throw new BusinessException('You cannot delete your own account.', [], 409);
        }

        if ($user->hasRole('super_admin')) {
            if (! $actor->hasRole('super_admin')) {
                throw new BusinessException('Only a super administrator can delete another super administrator.', [], 403);
            }

            if ($this->superAdminCount() <= 1) {
                throw new BusinessException('The last super administrator cannot be deleted.', [], 409);
            }
        }

        DB::transaction(function () use ($user, $actor): void {
            $context = ['user_id' => $user->id, 'user_email' => $user->email, 'changed_fields' => ['deleted']];
            $user->tokens()->delete();
            $user->syncRoles([]);
            $user->delete();
            $this->log->run('info', 'USER_DELETED', 'Admin user deleted', $context, $actor);
        });
    }

    private function assertMutableRole(Role $role): void
    {
        if ($role->name === 'super_admin') {
            throw new BusinessException('The system super_admin role cannot be changed or deleted.', [], 409);
        }
    }

    private function assertMayAssignRole(Role $role, User $actor): void
    {
        if ($role->name === 'super_admin' && ! $actor->hasRole('super_admin')) {
            throw new BusinessException('Only a super administrator can assign the super_admin role.', [], 403);
        }
    }

    private function assertNotRemovingLastSuperAdmin(User $user, Role $newRole): void
    {
        if ($user->hasRole('super_admin') && $newRole->name !== 'super_admin' && $this->superAdminCount() <= 1) {
            throw new BusinessException('The last super administrator must retain the super_admin role.', [], 409);
        }
    }

    private function superAdminCount(): int
    {
        return User::query()->whereHas('roles', fn ($query) => $query->where('name', 'super_admin')->where('guard_name', 'sanctum'))->count();
    }

    private function loadRole(Role $role): Role
    {
        return $role->load('permissions')->loadCount(['permissions', 'users']);
    }

    private function loadUser(User $user): User
    {
        return $user->load('roles.permissions')->loadCount('roles');
    }

    private function audit(string $event, string $message, Role $role, User $actor, array $changed): void
    {
        $this->log->run('info', $event, $message, [
            'role_id' => $role->id,
            'role_name' => $role->name,
            'changed_fields' => $changed,
        ], $actor);
    }

    private function auditUser(string $event, string $message, User $user, User $actor, array $changed): void
    {
        $this->log->run('info', $event, $message, [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'changed_fields' => $changed,
        ], $actor);
    }
}
