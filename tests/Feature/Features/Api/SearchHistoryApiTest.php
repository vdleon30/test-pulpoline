<?php

namespace Tests\Features\Api;

use App\Models\SearchHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SearchHistoryApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function authenticated_user_can_list_their_search_history()
    {
        Sanctum::actingAs($this->user);
        SearchHistory::factory()->count(5)->create(['user_id' => $this->user->id]);
        SearchHistory::factory()->count(2)->create(); // History for another user

        $response = $this->getJson(route('history.index'));

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data') // Assuming SearchHistoryResource collection is under 'data'
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'query_term', 'location_name', 'weather_data', 'searched_at']
                ],
                'links' => [], // For pagination
                'meta' => []   // For pagination
            ]);

        // Optionally, assert that the data belongs to the authenticated user
        $responseData = $response->json('data');
        foreach ($responseData as $historyItem) {
            $this->assertDatabaseHas('search_histories', ['id' => $historyItem['id'], 'user_id' => $this->user->id]);
        }
    }

    /** @test */
    public function unauthenticated_user_cannot_list_search_history()
    {
        $response = $this->getJson(route('history.index'), ['Accept' => 'application/json']);
        $response->assertStatus(401);
    }
}