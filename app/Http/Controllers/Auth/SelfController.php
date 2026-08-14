<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Controllers\Auth;

use App\Http\Responses\ApiResponse;

class SelfController
{
    public function __invoke()
    {

        $user = auth()->user()->load('roles.permissions');
        $permissions = $user->getAllPermissions()
            ->pluck('name')
            ->values();

        return ApiResponse::success([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified_at' => optional($user->email_verified_at)->toISOString(),
            'roles' => $user->roles->map(fn ($role) => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->map(fn ($permission) => [
                    'id' => $permission->id,
                    'name' => $permission->name,
                ])->values(),
            ])->values(),
            'permissions' => $permissions,
            'permission_names' => $permissions,
            'created_at' => optional($user->created_at)->toISOString(),
            'updated_at' => optional($user->updated_at)->toISOString(),
        ], 'OK', 200);

    }
}
