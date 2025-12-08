<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Support\Modul\AuthenticationAndRBAC\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ProtectSuperAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        Role::create(['name' => 'super-admin']);
        $adminRole = Role::create(['name' => 'admin']);
        
        // Create permissions
        $permissions = ['user.get', 'user.create', 'user.show', 'user.update', 'user.destroy'];
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }
        
        // Assign permissions to admin
        $adminRole->givePermissionTo($permissions);
        
        // Assign permissions to super-admin (or give all)
        $superAdminRole = Role::findByName('super-admin');
        $superAdminRole->givePermissionTo($permissions);
    }

    public function test_admin_cannot_create_super_admin()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $token = auth('api')->login($admin);

        $response = $this->postJson('/api/users', [
            'name' => 'New Super Admin',
            'email' => 'super@example.com',
            'password' => 'SpmbBengkulu2026!',
            'password_confirmation' => 'SpmbBengkulu2026!',
            'role' => 'super-admin',
        ], [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(422) // Validation error
            ->assertJsonValidationErrors(['role']);
    }

    public function test_admin_cannot_update_user_to_super_admin()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $token = auth('api')->login($admin);

        $targetUser = User::factory()->create();
        $targetUser->assignRole('admin');

        $response = $this->putJson("/api/users/{$targetUser->id}", [
            'name' => 'Updated User',
            'email' => $targetUser->email,
            'role' => 'super-admin',
        ], [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(422) // Validation error
            ->assertJsonValidationErrors(['role']);
    }

    public function test_admin_cannot_delete_super_admin()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $token = auth('api')->login($admin);

        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super-admin');

        $response = $this->deleteJson("/api/users/{$superAdmin->id}", [], [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(403); // Forbidden
    }

    public function test_super_admin_cannot_create_super_admin()
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super-admin');
        $token = auth('api')->login($superAdmin);

        $response = $this->postJson('/api/users', [
            'name' => 'Another Super Admin',
            'email' => 'another_super@example.com',
            'password' => 'SpmbBengkulu2026!',
            'password_confirmation' => 'SpmbBengkulu2026!',
            'role' => 'super-admin',
        ], [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(422) // Validation error from UserRequest
            ->assertJsonValidationErrors(['role']);
    }
}
