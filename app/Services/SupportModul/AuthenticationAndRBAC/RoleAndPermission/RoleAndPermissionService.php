<?php

namespace App\Services\SupportModul\AuthenticationAndRBAC\RoleAndPermission;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Exceptions\UnauthorizedException;

class RoleAndPermissionService
{
    public function getAll()
    {
        $roles = Role::with('permissions')->get()->map(function ($role) {
            return [
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name')->toArray(),
            ];
        });

        $permissions = Permission::pluck('name');

        return [
            'roles' => $roles,
            'permissions' => $permissions,
        ];
    }

    public function sync(array $data)
    {
        return DB::transaction(function () use ($data) {
            // 1. Sync Roles
            $requestedRoles = collect($data['roles']);
            $requestedRoleNames = $requestedRoles->pluck('name')->toArray();
            $existingRoles = Role::pluck('name')->toArray();

            // Protect super-admin role from deletion
            if (!in_array('super-admin', $requestedRoleNames)) {
                throw new UnauthorizedException(403, 'Role super-admin tidak boleh dihapus.');
            }

            // Create new roles
            $newRoles = array_diff($requestedRoleNames, $existingRoles);
            foreach ($newRoles as $roleName) {
                Role::create(['name' => $roleName]);
            }

            // 3. Sync Assignments
            foreach ($requestedRoles as $roleData) {
                $role = Role::findByName($roleData['name']);
                
                // Protect super-admin permissions
                if ($role->name === 'super-admin') {
                    $role->syncPermissions(Permission::all());
                }

                $role->syncPermissions($roleData['permissions']);
            }

            return $this->getAll();
        });
    }
}
