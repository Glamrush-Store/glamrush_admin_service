<?php

namespace App\Http\Controllers\AccessControl;

use App\Domain\AccessControl\Services\AccessControlService;
use App\Http\Requests\AccessControl\CreateRoleRequest;
use App\Http\Requests\AccessControl\ListRolesRequest;
use App\Http\Requests\AccessControl\SyncRolePermissionsRequest;
use App\Http\Requests\AccessControl\UpdateRoleRequest;
use App\Http\Resources\AccessControl\PermissionResource;
use App\Http\Resources\AccessControl\RoleResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController
{
    public function __construct(private AccessControlService $service) {}

    public function index(ListRolesRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $roles = Role::query()->where('guard_name', 'sanctum')
            ->withCount(['permissions', 'users'])
            ->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy($filters['sort'] ?? 'name', $filters['direction'] ?? 'asc')
            ->paginate($filters['per_page'] ?? 20)->withQueryString();

        return ApiResponse::success(RoleResource::collection($roles));
    }

    public function permissions(Request $request): JsonResponse
    {
        $permissions = Permission::query()->where('guard_name', 'sanctum')->orderBy('name')->get();

        return ApiResponse::success(PermissionResource::collection($permissions));
    }

    public function store(CreateRoleRequest $request): JsonResponse
    {
        return ApiResponse::success(new RoleResource($this->service->createRole($request->validated(), $request->user())), 'Role created', 201);
    }

    public function show(Role $role): JsonResponse
    {
        $this->ensureSanctumRole($role);

        return ApiResponse::success(new RoleResource($role->load('permissions')->loadCount(['permissions', 'users'])));
    }

    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        $this->ensureSanctumRole($role);

        return ApiResponse::success(new RoleResource($this->service->updateRole($role, $request->validated(), $request->user())), 'Role updated');
    }

    public function syncPermissions(SyncRolePermissionsRequest $request, Role $role): JsonResponse
    {
        $this->ensureSanctumRole($role);

        return ApiResponse::success(new RoleResource($this->service->syncRolePermissions($role, $request->validated('permissions'), $request->user())), 'Role permissions updated');
    }

    public function destroy(Request $request, Role $role): JsonResponse
    {
        $this->ensureSanctumRole($role);
        $this->service->deleteRole($role, $request->user());

        return ApiResponse::success(null, 'Role deleted');
    }

    private function ensureSanctumRole(Role $role): void
    {
        abort_unless($role->guard_name === 'sanctum', 404);
    }
}
