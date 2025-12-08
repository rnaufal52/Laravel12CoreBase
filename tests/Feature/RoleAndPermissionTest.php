<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Support\Modul\AuthenticationAndRBAC\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleAndPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed initial roles/permissions if needed, or rely on what's created in test
        Role::create(['name' => 'super-admin']);
    }

    public function test_only_super_admin_can_access_endpoint()
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $user->assignRole('admin');
        $token = auth('api')->login($user);

        $response = $this->getJson('/api/roles-and-permissions', [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(403);
    }

    public function test_get_all_roles_and_permissions()
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super-admin');
        $token = auth('api')->login($superAdmin);

        Permission::create(['name' => 'test.permission']);

        $response = $this->getJson('/api/roles-and-permissions', [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'roles',
                    'permissions',
                ],
            ]);
    }

    public function test_sync_roles_and_permissions()
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super-admin');
        $token = auth('api')->login($superAdmin);

        // Create existing permission
        Permission::create(['name' => 'user.view']);
        Permission::create(['name' => 'user.create']);

        $payload = [
            // 'permissions' => ... (removed)
            'roles' => [
                [
                    'name' => 'super-admin',
                    'permissions' => ['user.create', 'user.view'], // Assign existing
                ],
                [
                    'name' => 'new-role',
                    'permissions' => ['user.view'], // Only valid permissions allowed
                ],
            ],
        ];

        // Adjust payload to only use existing permissions for now to ensure success
        $payload['roles'][1]['permissions'] = ['user.view'];

        $response = $this->putJson('/api/roles-and-permissions', $payload, [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Role dan permission berhasil diperbarui',
            ]);

        // Verify Database
        $this->assertDatabaseHas('roles', ['name' => 'new-role']);
        
        $newRole = Role::findByName('new-role');
        $this->assertTrue($newRole->hasPermissionTo('user.view'));
    }

    public function test_roles_are_not_deleted_when_omitted()
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super-admin');
        $token = auth('api')->login($superAdmin);

        $roleToDelete = Role::create(['name' => 'role-to-delete']);
        Permission::create(['name' => 'user.view']);

        $payload = [
            'roles' => [
                [
                    'name' => 'super-admin', // Only sending super-admin
                    'permissions' => ['user.view'],
                ],
            ],
        ];

        $response = $this->putJson('/api/roles-and-permissions', $payload, [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(200);
        
        // Verify 'role-to-delete' still exists
        $this->assertDatabaseHas('roles', ['name' => 'role-to-delete']);
    }

    public function test_update_with_invalid_permission_returns_422()
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super-admin');
        $token = auth('api')->login($superAdmin);

        $payload = [
            'roles' => [
                [
                    'name' => 'super-admin',
                    'permissions' => ['non.existent.permission'],
                ],
            ],
        ];

        $response = $this->putJson('/api/roles-and-permissions', $payload, [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['roles.0.permissions.0']);
    }

    public function test_update_without_super_admin_returns_422()
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super-admin');
        $token = auth('api')->login($superAdmin);

        $payload = [
            'roles' => [
                [
                    'name' => 'admin',
                    'permissions' => [],
                ],
            ],
        ];

        $response = $this->putJson('/api/roles-and-permissions', $payload, [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'errors' => [
                    'roles' => [
                        'Super admin tidak boleh dihapus.',
                    ],
                ],
            ]);
    }
}
