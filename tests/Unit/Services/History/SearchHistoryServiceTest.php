<?php

namespace Tests\Unit\Services\History;

use App\Contracts\History\SearchHistoryServiceInterface;
use App\Models\SearchHistory;
use App\Models\User;
use App\Services\History\SearchHistoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchHistoryServiceTest extends TestCase
{
    use RefreshDatabase;

    private SearchHistoryServiceInterface $searchHistoryService;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->searchHistoryService = new SearchHistoryService();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_log_a_weather_search()
    {
        $queryTerm = 'London';
        $weatherData = [
            'city' => 'London',
            'temperature_celsius' => 15.0,
            'condition' => 'Partly cloudy',
            'wind_kph' => 10.0,
            'humidity_percent' => 70,
            'local_time' => '2023-10-27 10:00',
        ];

        $historyEntry = $this->searchHistoryService->logSearch($this->user, $queryTerm, $weatherData);

        $this->assertInstanceOf(SearchHistory::class, $historyEntry);
        $this->assertEquals($this->user->id, $historyEntry->user_id);
        $this->assertEquals($queryTerm, $historyEntry->query_term);
        $this->assertEquals($weatherData['city'], $historyEntry->location_name);
        $this->assertEquals($weatherData, $historyEntry->weather_data);
        $this->assertDatabaseHas('search_histories', ['user_id' => $this->user->id, 'query_term' => $queryTerm]);
    }
}