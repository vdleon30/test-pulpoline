<?php

namespace Tests\Unit\Services\Favorite;

use App\Contracts\Favorite\FavoriteCityServiceInterface;
use App\Models\FavoriteCity;
use App\Models\User;
use App\Services\Favorite\FavoriteCityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class FavoriteCityServiceTest extends TestCase
{
    use RefreshDatabase;

    private FavoriteCityServiceInterface $favoriteCityService;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->favoriteCityService = new FavoriteCityService();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_get_user_favorites_paginated()
    {
        FavoriteCity::factory()->count(5)->create(['user_id' => $this->user->id]);

        $favoritesPaginator = $this->favoriteCityService->getUserFavorites($this->user, 3);

        $this->assertInstanceOf(LengthAwarePaginator::class, $favoritesPaginator);
        $this->assertEquals(3, $favoritesPaginator->perPage());
        $this->assertEquals(5, $favoritesPaginator->total());
    }

    /** @test */
    public function it_can_add_a_new_favorite_city()
    {
        $cityName = 'Paris';
        $favorite = $this->favoriteCityService->addFavorite($this->user, $cityName);

        $this->assertInstanceOf(FavoriteCity::class, $favorite);
        $this->assertEquals($cityName, $favorite->city_name);
        $this->assertEquals($this->user->id, $favorite->user_id);
        $this->assertDatabaseHas('favorite_cities', [
            'user_id' => $this->user->id,
            'city_name' => $cityName,
        ]);
    }

    /** @test */
    public function it_returns_null_when_adding_an_existing_favorite_city()
    {
        $cityName = 'London';
        FavoriteCity::factory()->create([
            'user_id' => $this->user->id,
            'city_name' => $cityName,
        ]);

        $favorite = $this->favoriteCityService->addFavorite($this->user, $cityName);

        $this->assertNull($favorite);
        $this->assertDatabaseCount('favorite_cities', 1); // Ensure no duplicate was created
    }

    /** @test */
    public function it_can_remove_an_existing_favorite_city()
    {
        $cityName = 'Berlin';
        FavoriteCity::factory()->create([
            'user_id' => $this->user->id,
            'city_name' => $cityName,
        ]);

        $this->assertDatabaseHas('favorite_cities', ['city_name' => $cityName, 'user_id' => $this->user->id]);

        $wasRemoved = $this->favoriteCityService->removeFavorite($this->user, $cityName);

        $this->assertTrue($wasRemoved);
        $this->assertDatabaseMissing('favorite_cities', [
            'user_id' => $this->user->id,
            'city_name' => $cityName,
        ]);
    }

    /** @test */
    public function it_returns_false_when_removing_a_non_existent_favorite_city()
    {
        $cityName = 'Madrid'; // This city is not a favorite

        $wasRemoved = $this->favoriteCityService->removeFavorite($this->user, $cityName);

        $this->assertFalse($wasRemoved);
        $this->assertDatabaseMissing('favorite_cities', [
            'user_id' => $this->user->id,
            'city_name' => $cityName,
        ]);
    }
}