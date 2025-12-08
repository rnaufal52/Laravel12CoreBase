<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Support\Modul\AuthenticationAndRBAC\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ExceptionHandlingTest extends TestCase
{
    use RefreshDatabase;

    public function test_404_returns_json()
    {
        $response = $this->getJson('/api/non-existent-route');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Resource tidak ditemukan',
            ]);
    }

    public function test_403_returns_json()
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'user']);
        $user->assignRole('user');
        
        // Create a permission but don't give it to the user
        Permission::create(['name' => 'user.create']);

        $token = auth('api')->login($user);

        // Try to access a protected route that requires permission
        // Assuming POST /api/users requires user.create permission
        $response = $this->postJson('/api/users', [], [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk tindakan ini',
            ]);
    }

    public function test_500_returns_json()
    {
        // Define a route that throws an exception
        \Illuminate\Support\Facades\Route::get('/api/test-500', function () {
            throw new \Exception('Test Exception');
        });

        $response = $this->getJson('/api/test-500');

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
            ]);
            
        // Message might vary depending on debug mode, so we just check success false and status 500
    }

    public function test_generic_http_exception_returns_json()
    {
        // Define a route that aborts with 400
        \Illuminate\Support\Facades\Route::get('/api/test-400', function () {
            abort(400, 'Bad Request Test');
        });

        $response = $this->getJson('/api/test-400');

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Bad Request Test',
            ]);
    }
}
