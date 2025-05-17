<?php

namespace App\Services\History;

use App\Contracts\History\SearchHistoryServiceInterface;
use App\Models\SearchHistory;
use App\Models\User;

class SearchHistoryService implements SearchHistoryServiceInterface
{
    /**
     * Log a weather search for a user.
     *
     * @param User $user The user who performed the search.
     * @param string $queryTerm The original search term.
     * @param array $weatherData The weather data received from the weather service.
     * @return \App\Models\SearchHistory The created search history record.
     */
    public function logSearch(User $user, string $queryTerm, array $weatherData): SearchHistory
    {
        return SearchHistory::create([
            'user_id' => $user->id,
            'query_term' => $queryTerm,
            'location_name' => $weatherData['city'] ?? $queryTerm, 
            'weather_data' => $weatherData,
        ]);
    }
}