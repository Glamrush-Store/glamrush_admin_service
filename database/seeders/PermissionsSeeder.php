<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {

        app()[\Spatie\Permission\PermissionRegistrar::class]
            ->forgetCachedPermissions();

        // All models that use this permission pattern
        $models = [
            'Role',
            'User',
            'Product',
            'ProductVariant',
            'Category',
            'Brand',
            'Vendor',
            'SkuAttributeCode'
            // Add more models here...
        ];

        // All actions your policies expect
        $actions = [
            'ViewAny',
            'View',
            'Create',
            'Update',
            'Delete',
            'Restore',
        ];

        $allPermissions = [];

        foreach ($models as $model) {
            foreach ($actions as $action) {
                $name = "{$action}_{$model}";
                $permission = Permission::firstOrCreate(['name' => $name]);
                $allPermissions[] = $permission;
            }
        }

        // Create or fetch the super_admin role
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);

        // Give super_admin every generated permission
        $superAdmin->syncPermissions($allPermissions);

        // Optional: assign super_admin to a specific user
        if ($user = User::first()) {
            $user->assignRole($superAdmin);
        }
    }
}
