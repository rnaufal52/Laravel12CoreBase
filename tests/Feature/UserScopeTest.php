<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Support\Modul\AuthenticationAndRBAC\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

class UserScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_scope_filter()
    {
        // Create roles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'user']);

        // Create users
        $user1 = User::factory()->create(['name' => 'Alice', 'email' => 'alice@example.com', 'created_at' => now()->subDays(2)]);
        $user1->assignRole('admin');

        $user2 = User::factory()->create(['name' => 'Bob', 'email' => 'bob@example.com', 'created_at' => now()->subDay()]);
        $user2->assignRole('user');

        $user3 = User::factory()->create(['name' => 'Charlie', 'email' => 'charlie@example.com', 'created_at' => now()]);
        $user3->assignRole('user');

        // Test Search
        $results = User::filter(['search' => 'Alice'])->get();
        $this->assertCount(1, $results);
        $this->assertEquals('Alice', $results->first()->name);

        $results = User::filter(['search' => 'example.com'])->get();
        $this->assertCount(3, $results);

        // Test Role Filter
        $results = User::filter(['role' => 'admin'])->get();
        $this->assertCount(1, $results);
        $this->assertEquals('Alice', $results->first()->name);

        $results = User::filter(['role' => 'user'])->get();
        $this->assertCount(2, $results);

        // Test Sort
        $results = User::filter(['sort' => 'name', 'direction' => 'desc'])->get();
        $this->assertEquals('Charlie', $results->first()->name);
        $this->assertEquals('Alice', $results->last()->name);

        $results = User::filter(['sort' => 'created_at', 'direction' => 'asc'])->get();
        $this->assertEquals('Alice', $results->first()->name);
        $this->assertEquals('Charlie', $results->last()->name);
    }
}
