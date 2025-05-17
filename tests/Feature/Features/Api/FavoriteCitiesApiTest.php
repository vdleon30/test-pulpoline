<?php

namespace Tests\Features\Api;

use App\Models\FavoriteCity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FavoriteCitiesApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function authenticated_user_can_list_their_favorite_cities()
    {
        Sanctum::actingAs($this->user);
        FavoriteCity::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->getJson(route('favorites.index'));

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data') // Assuming FavoriteCityResource collection is under 'data'
            ->assertJsonStructure(['data' => [['id', 'city_name', 'added_at']]]);
    }

    /** @test */
    public function unauthenticated_user_cannot_list_favorite_cities()
    {
        $response = $this->getJson(route('favorites.index'), ['Accept' => 'application/json']);
        $response->assertStatus(401);
    }

    /** @test */
    public function authenticated_user_can_add_a_favorite_city()
    {
        Sanctum::actingAs($this->user);
        $cityName = 'New York';

        $response = $this->postJson(route('favorites.store'), ['city_name' => $cityName]);

        $response->assertStatus(201) // Or 200 if your store method returns 200 on success
            ->assertJsonPath('data.city_name', $cityName);
        $this->assertDatabaseHas('favorite_cities', ['user_id' => $this->user->id, 'city_name' => $cityName]);
    }

    /** @test */
    public function authenticated_user_cannot_add_an_existing_favorite_city()
    {
        Sanctum::actingAs($this->user);
        $cityName = 'Paris';
        FavoriteCity::factory()->create(['user_id' => $this->user->id, 'city_name' => $cityName]);

        $response = $this->postJson(route('favorites.store'), ['city_name' => $cityName]);

        $response->assertStatus(409) // Conflict
            ->assertJson(['message' => 'City already in favorites.']);
    }

    /** @test */
    public function adding_a_favorite_city_requires_city_name()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson(route('favorites.store'), []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['city_name']);
    }

    /** @test */
    public function unauthenticated_user_cannot_add_a_favorite_city()
    {
        $response = $this->postJson(route('favorites.store'), ['city_name' => 'Tokyo']);
        $response->assertStatus(401);
    }

    /** @test */
    public function authenticated_user_can_remove_a_favorite_city()
    {
        Sanctum::actingAs($this->user);
        $cityName = 'Berlin';
        FavoriteCity::factory()->create(['user_id' => $this->user->id, 'city_name' => $cityName]);

        $response = $this->deleteJson(route('favorites.destroy', ['city_name' => $cityName]));

        $response->assertStatus(200)
            ->assertJson(['message' => 'City removed from favorites.']);
        $this->assertDatabaseMissing('favorite_cities', ['user_id' => $this->user->id, 'city_name' => $cityName]);
    }

    /** @test */
    public function authenticated_user_receives_404_when_removing_non_existent_favorite_city()
    {
        Sanctum::actingAs($this->user);
        $cityName = 'NonExistentCity';

        $response = $this->deleteJson(route('favorites.destroy', ['city_name' => $cityName]));

        $response->assertStatus(404)
            ->assertJson(['message' => 'City not found in favorites.']);
    }

    /** @test */
    public function unauthenticated_user_cannot_remove_a_favorite_city()
    {
        // No need to create a favorite, just check auth
        $response = $this->deleteJson(route('favorites.destroy', ['city_name' => 'SomeCity']));
        $response->assertStatus(401);
    }
}