<?php

namespace App\Contracts\History;

use App\Models\User;
use App\Models\SearchHistory;

/**
 * Interface SearchHistoryServiceInterface
 *
 * Defines the contract for a service that manages search history.
 */
interface SearchHistoryServiceInterface
{
    /**
     * Log a weather search for a user.
     *
     * @param User $user The user who performed the search.
     * @param string $queryTerm The original search term.
     * @param array $weatherData The weather data received from the weather service.
     * @return \App\Models\SearchHistory The created search history record.
     */
    public function logSearch(User $user, string $queryTerm, array $weatherData): SearchHistory;
}