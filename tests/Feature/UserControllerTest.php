<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Support\Modul\AuthenticationAndRBAC\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $role = Role::create(['name' => 'admin']);
        Role::create(['name' => 'user']);

        // Create permissions
        $permissions = ['user.get', 'user.create', 'user.show', 'user.update', 'user.destroy'];
        foreach ($permissions as $permission) {
            \Spatie\Permission\Models\Permission::create(['name' => $permission]);
        }

        // Assign permissions to admin
        $role->givePermissionTo($permissions);
    }

    public function test_index_returns_paginated_users()
    {
        User::factory()->count(15)->create();
        $user = User::factory()->create();
        $user->assignRole('admin');
        $token = auth('api')->login($user);

        $response = $this->getJson('/api/users', [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'roles' => [
                            '*' => [
                                'id',
                                'name',
                            ],
                        ],
                    ],
                ],
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ],
            ])->assertJsonMissing(['pivot']);
    }

    public function test_show_returns_user_with_roles_without_pivot()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $token = auth('api')->login($user);

        $response = $this->getJson("/api/users/{$user->id}", [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'roles' => [
                        '*' => [
                            'id',
                            'name',
                        ],
                    ],
                ],
            ])->assertJsonMissing(['pivot']);
    }

    public function test_store_creates_new_user()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $token = auth('api')->login($user);

        $response = $this->postJson('/api/users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'SpmbBengkulu2026!',
            'password_confirmation' => 'SpmbBengkulu2026!',
            'role' => 'user',
        ], [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'User berhasil ditambahkan',
            ]);

        $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);
        
        $newUser = User::where('email', 'newuser@example.com')->first();
        $this->assertTrue($newUser->hasRole('user'));
    }

    public function test_update_user_with_same_email()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $token = auth('api')->login($admin);

        $userToUpdate = User::factory()->create(['email' => 'existing@example.com']);
        $userToUpdate->assignRole('user');

        $response = $this->putJson("/api/users/{$userToUpdate->id}", [
            'name' => 'Updated Name',
            'email' => 'existing@example.com', // Same email
            'role' => 'user',
        ], [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'User berhasil diperbarui',
                'data' => [
                    'id' => $userToUpdate->id,
                    'name' => 'Updated Name',
                    'email' => 'existing@example.com',
                ],
            ]);
    }

    public function test_update_non_existent_user_returns_404()
    {
        $admin = User::factory()->create(['email' => 'admin@example.com']);
        $admin->assignRole('admin');
        $token = auth('api')->login($admin);

        $response = $this->putJson('/api/users/99999', [
            'name' => 'Updated Name',
            'email' => 'admin@example.com', // Use existing email to trigger validation error if 404 check fails
            'role' => 'user',
        ], [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'User tidak ditemukan',
            ]);
    }

    public function test_delete_non_existent_user_returns_404()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $token = auth('api')->login($admin);

        $response = $this->deleteJson('/api/users/99999', [], [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'User tidak ditemukan',
            ]);
    }
}
