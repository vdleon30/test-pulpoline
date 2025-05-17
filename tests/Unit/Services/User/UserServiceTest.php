<?php

namespace Tests\Unit\Services\User;

use App\Contracts\User\UserServiceInterface;
use App\Models\User;
use App\Services\User\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    private UserServiceInterface $userService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userService = new UserService();

        // Ensure default roles exist for tests that rely on them
        Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    }

    /** @test */
    public function it_can_get_all_users_paginated()
    {
        User::factory()->count(20)->create();

        $users = $this->userService->getAllUsers();

        $this->assertInstanceOf(LengthAwarePaginator::class, $users);
        $this->assertEquals(15, $users->perPage()); // Default perPage
        $this->assertCount(15, $users->items());
        $this->assertEquals(20, $users->total());
    }

    /** @test */
    public function it_can_filter_users_by_search_term()
    {
        User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
        User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);
        User::factory()->create(['name' => 'Peter Jones', 'email' => 'peter.doe@example.com']); // Name doesn't match, email does

        $usersByName = $this->userService->getAllUsers(['search' => 'John']);
        $this->assertCount(1, $usersByName->items());
        $this->assertEquals('John Doe', $usersByName->first()->name);

        $usersByEmail = $this->userService->getAllUsers(['search' => 'doe@example.com']);
        $this->assertCount(1, $usersByEmail->items()); // Should only find peter.doe@example.com by email search
        $this->assertEquals('Peter Jones', $usersByEmail->first()->name);
    }

    /** @test */
    public function it_can_create_a_new_user_with_default_role()
    {
        $userData = [
            'name' => 'Test User Create',
            'email' => 'create@example.com',
            'password' => 'password123',
        ];

        $user = $this->userService->createUser($userData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('Test User Create', $user->name);
        $this->assertEquals('create@example.com', $user->email);
        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertDatabaseHas('users', ['email' => 'create@example.com']);
        $this->assertTrue($user->hasRole('user')); // Checks default role assignment
    }

    /** @test */
    public function it_can_create_a_new_user_with_specified_roles()
    {
        $userData = [
            'name' => 'Admin Test User',
            'email' => 'admin.create@example.com',
            'password' => 'password123',
            'roles' => ['admin']
        ];

        $user = $this->userService->createUser($userData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertTrue($user->hasRole('admin'));
        $this->assertFalse($user->hasRole('user')); // Should not have default if specific is provided
    }

    /** @test */
    public function it_can_get_a_user_by_id()
    {
        $createdUser = User::factory()->create();
        $foundUser = $this->userService->getUserById($createdUser->id);

        $this->assertInstanceOf(User::class, $foundUser);
        $this->assertEquals($createdUser->id, $foundUser->id);
    }

    /** @test */
    public function it_returns_null_when_getting_a_non_existent_user_by_id()
    {
        $foundUser = $this->userService->getUserById(9999);
        $this->assertNull($foundUser);
    }

    /** @test */
    public function it_can_update_an_existing_user()
    {
        $user = User::factory()->create(['name' => 'Old Name']);
        $updateData = [
            'name' => 'New Updated Name',
            'email' => 'new.update@example.com',
        ];

        $updatedUser = $this->userService->updateUser($user, $updateData);

        $this->assertEquals('New Updated Name', $updatedUser->name);
        $this->assertEquals('new.update@example.com', $updatedUser->email);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'New Updated Name']);
    }

    /** @test */
    public function it_can_update_user_password_if_provided()
    {
        $user = User::factory()->create();
        $updateData = ['password' => 'newSecurePassword123'];

        $this->userService->updateUser($user, $updateData);
        $user->refresh();

        $this->assertTrue(Hash::check('newSecurePassword123', $user->password));
    }

    /** @test */
    public function it_can_update_user_roles()
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $updateData = ['roles' => ['admin']];
        $this->userService->updateUser($user, $updateData);

        $user->refresh(); // Refresh to get updated roles
        $this->assertTrue($user->hasRole('admin'));
        $this->assertFalse($user->hasRole('user'));
    }

    /** @test */
    public function it_can_delete_a_user()
    {
        $user = User::factory()->create();
        $this->assertDatabaseHas('users', ['id' => $user->id]);

        $result = $this->userService->deleteUser($user);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}