<?php

namespace Tests\Features\Api\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $regularUser;
    protected Role $adminRole;
    protected Role $userRole;
    protected Permission $manageUsersPermission;

    protected function setUp(): void
    {
        parent::setUp();

        // Create permissions
        $this->manageUsersPermission = Permission::firstOrCreate(['name' => 'manage users', 'guard_name' => 'web']);

        // Create roles
        $this->adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $this->userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        // Assign permissions to roles
        $this->adminRole->givePermissionTo($this->manageUsersPermission);

        // Create users
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole($this->adminRole);

        $this->regularUser = User::factory()->create();
        $this->regularUser->assignRole($this->userRole);
    }

    // --- INDEX ---
    /** @test */
    public function admin_can_list_all_users()
    {
        Sanctum::actingAs($this->adminUser, ['*']);
        User::factory()->count(3)->create();

        $response = $this->getJson(route('users.index'));

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data'); // 3 created + admin + regularUser
    }

    /** @test */
    public function regular_user_cannot_list_all_users()
    {
        Sanctum::actingAs($this->regularUser, ['*']);

        $response = $this->getJson(route('users.index'));

        $response->assertStatus(403); // Forbidden due to route middleware or controller authorize
    }

    /** @test */
    public function unauthenticated_user_cannot_list_users()
    {
        $response = $this->getJson(route('users.index'));
        $response->assertStatus(401);
    }

    /** @test */
    public function admin_can_search_users()
    {
        Sanctum::actingAs($this->adminUser, ['*']);
        User::factory()->create(['name' => 'John Doe Searchable', 'email' => 'john.search@example.com']);

        $response = $this->getJson(route('users.index', ['search' => 'Searchable']));
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'John Doe Searchable');
    }

    // --- STORE ---
    /** @test */
    public function admin_can_create_a_new_user()
    {
        Sanctum::actingAs($this->adminUser, ['*']);
        $userData = [
            'name' => 'New Test User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => ['user']
        ];

        $response = $this->postJson(route('users.store'), $userData);

        $response->assertStatus(201) // Assuming UserResource returns 201 from controller
            ->assertJsonPath('data.name', 'New Test User')
            ->assertJsonPath('data.email', 'newuser@example.com');

        $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);
        $newUser = User::whereEmail('newuser@example.com')->first();
        $this->assertTrue($newUser->hasRole('user'));
    }

    /** @test */
    public function store_user_requires_valid_data()
    {
        Sanctum::actingAs($this->adminUser, ['*']);

        $response = $this->postJson(route('users.store'), ['name' => 'Test']); // Missing email, password
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    /** @test */
    public function regular_user_cannot_create_a_user()
    {
        Sanctum::actingAs($this->regularUser, ['*']);
        $userData = [
            'name' => 'Attempted User',
            'email' => 'attempt@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson(route('users.store'), $userData);
        $response->assertStatus(403); // Forbidden by StoreUserRequest authorize
    }

    // --- SHOW ---
    /** @test */
    public function admin_can_view_any_user()
    {
        Sanctum::actingAs($this->adminUser, ['*']);
        $userToView = User::factory()->create();

        $response = $this->getJson(route('users.show', $userToView->id));

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $userToView->id);
    }

    /** @test */
    public function regular_user_cannot_view_another_users_profile()
    {
        Sanctum::actingAs($this->regularUser, ['*']);
        $otherUser = User::factory()->create();

        $response = $this->getJson(route('users.show', $otherUser->id));

        $response->assertStatus(403); // Forbidden by controller logic
    }

    /** @test */
    public function show_returns_404_for_non_existent_user()
    {
        Sanctum::actingAs($this->adminUser, ['*']);
        $response = $this->getJson(route('users.show', 9999)); // Non-existent ID
        $response->assertStatus(404);
    }

    // --- UPDATE ---
    /** @test */
    public function admin_can_update_any_user()
    {
        Sanctum::actingAs($this->adminUser, ['*']);
        $userToUpdate = User::factory()->create();
        $updateData = ['name' => 'Updated Name by Admin', 'roles' => ['user']];

        $response = $this->putJson(route('users.update', $userToUpdate->id), $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Name by Admin');
        $this->assertDatabaseHas('users', ['id' => $userToUpdate->id, 'name' => 'Updated Name by Admin']);
        $userToUpdate->refresh();
        $this->assertTrue($userToUpdate->hasRole('user'));
    }

    /** @test */
    public function regular_user_cannot_update_another_users_profile()
    {
        Sanctum::actingAs($this->regularUser, ['*']);
        $otherUser = User::factory()->create();
        $updateData = ['name' => 'Attempted Update'];

        $response = $this->putJson(route('users.update', $otherUser->id), $updateData);

        $response->assertStatus(403); // Forbidden by UpdateUserRequest authorize
    }

    /** @test */
    public function update_user_validates_data()
    {
        Sanctum::actingAs($this->adminUser, ['*']);
        $userToUpdate = User::factory()->create();
        $updateData = ['email' => 'notanemail'];

        $response = $this->putJson(route('users.update', $userToUpdate->id), $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    // --- DESTROY ---
    /** @test */
    public function admin_can_delete_a_user()
    {
        Sanctum::actingAs($this->adminUser, ['*']);
        $userToDelete = User::factory()->create();
        $userToDelete->assignRole($this->userRole); // Ensure it's not an admin for this test

        $response = $this->deleteJson(route('users.destroy', $userToDelete->id));

        $response->assertStatus(200)
            ->assertJson(['message' => 'User deleted successfully.']);
        $this->assertDatabaseMissing('users', ['id' => $userToDelete->id]);
    }

    /** @test */
    public function admin_cannot_delete_themselves()
    {
        Sanctum::actingAs($this->adminUser, ['*']);

        $response = $this->deleteJson(route('users.destroy', $this->adminUser->id));

        $response->assertStatus(403) // Forbidden by controller logic
            ->assertJson(['message' => 'Admin user cannot delete themselves.']);
        $this->assertDatabaseHas('users', ['id' => $this->adminUser->id]);
    }

    /** @test */
    public function regular_user_cannot_delete_a_user()
    {
        Sanctum::actingAs($this->regularUser, ['*']);
        $userToDelete = User::factory()->create();

        $response = $this->deleteJson(route('users.destroy', $userToDelete->id));

        $response->assertStatus(403); // Forbidden by route middleware 'permission:manage users'
    }

    /** @test */
    public function destroy_returns_404_for_non_existent_user()
    {
        Sanctum::actingAs($this->adminUser, ['*']);
        $response = $this->deleteJson(route('users.destroy', 9999)); // Non-existent ID
        $response->assertStatus(404);
    }
}
