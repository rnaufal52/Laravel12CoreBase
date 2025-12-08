<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Support\Modul\AuthenticationAndRBAC\User;
use App\Services\SupportModul\AuthenticationAndRBAC\User\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;

class UserServicePaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_all_returns_paginated_result()
    {
        User::factory()->count(15)->create();

        $service = new UserService();
        $result = $service->getAll();

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(10, $result->perPage());
        $this->assertEquals(15, $result->total());
        $this->assertCount(10, $result->items());
    }

    public function test_get_all_supports_custom_per_page()
    {
        User::factory()->count(15)->create();

        $service = new UserService();
        $result = $service->getAll([], 5);

        $this->assertEquals(5, $result->perPage());
        $this->assertCount(5, $result->items());
    }

    public function test_get_all_supports_filtering()
    {
        User::factory()->create(['name' => 'Alice']);
        User::factory()->create(['name' => 'Bob']);

        $service = new UserService();
        $result = $service->getAll(['search' => 'Alice']);

        $this->assertCount(1, $result->items());
        $this->assertEquals('Alice', $result->items()[0]->name);
    }
}
