<?php

namespace App\Http\Controllers\AccessControl;

use App\Domain\AccessControl\Services\AccessControlService;
use App\Http\Requests\AccessControl\CreateUserRequest;
use App\Http\Requests\AccessControl\ListUsersRequest;
use App\Http\Requests\AccessControl\UpdateUserRequest;
use App\Http\Resources\AccessControl\UserResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController
{
    public function __construct(private AccessControlService $service) {}

    public function index(ListUsersRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $users = User::query()->with('roles')
            ->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query->where(fn (Builder $query) => $query->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")))
            ->when($filters['role_id'] ?? null, fn (Builder $query, int $roleId) => $query->whereHas('roles', fn (Builder $query) => $query->where('roles.id', $roleId)))
            ->orderBy($filters['sort'] ?? 'name', $filters['direction'] ?? 'asc')
            ->paginate($filters['per_page'] ?? 20)->withQueryString();

        return ApiResponse::success(UserResource::collection($users));
    }

    public function store(CreateUserRequest $request): JsonResponse
    {
        return ApiResponse::success(new UserResource($this->service->createUser($request->validated(), $request->user())), 'User created', 201);
    }

    public function show(User $user): JsonResponse
    {
        return ApiResponse::success(new UserResource($user->load('roles.permissions')));
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        return ApiResponse::success(new UserResource($this->service->updateUser($user, $request->validated(), $request->user())), 'User updated');
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        $this->service->deleteUser($user, $request->user());

        return ApiResponse::success(null, 'User deleted');
    }
}
