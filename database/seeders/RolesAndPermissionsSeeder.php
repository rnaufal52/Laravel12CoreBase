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
        // role
        $roles=[
            'super-admin',
            'admin',
            'staff'
        ];

        // permission
        $permissions=[
            'user' => ['get', 'create', 'show', 'update', 'destroy'],
        ];

        // role has permission
        $RolePermission=[
            'super-admin'=>[
                'user'=>'*',
            ],
            'admin'=>[
                'user'=>'*',
            ],
        ];

        foreach($roles as $role){
            Role::firstOrCreate(['name' => $role]);
        }

        foreach($permissions as $permission => $types){
            foreach($types as $type){
                Permission::firstOrCreate(['name' => $permission.'.'.$type]);
            }
        }

        foreach($RolePermission as $r => $perms){
            $role = Role::findByName($r);
            foreach($perms as $perm => $p){
                if($perms[$perm] == '*'){
                    foreach($permissions[$perm] as $prm){
                        $role->givePermissionTo($perm.'.'.$prm);
                    }
                }else{
                    foreach($p as $prm){
                        $role->givePermissionTo($perm.'.'.$prm);
                    }
                }
            }
        }
    }
}
