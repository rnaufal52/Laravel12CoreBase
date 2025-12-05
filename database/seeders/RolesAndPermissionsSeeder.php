<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define permissions by module
        $permissionsByEntity = [
            'user' => [
                'manage',
                'create_siswa',
                'update_self',
                'create_self',
            ],
        ];

        // Create permissions
        foreach ($permissionsByEntity as $entity => $permissions) {
            foreach ($permissions as $permission) {
                Permission::firstOrCreate(['name' => "$entity.$permission"]);
            }
        }

        // Define roles and their permissions
        $roles = [
            'super-admin' => 'all',
            'admin' => 'all',
            'staff' => [
                'user.create_siswa',
                'user.update_self',
            ],
            'siswa' => [
                'user.create_self',
                'user.update_self',
            ],
        ];

        // Create roles and assign permissions
        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);

            if ($rolePermissions === 'all') {
                $role->givePermissionTo(Permission::all());
            } else {
                $role->givePermissionTo($rolePermissions);
            }
        }
    }
}
