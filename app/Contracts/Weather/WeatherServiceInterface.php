<?php

namespace App\Contracts\Weather;

/**
 * Interface WeatherServiceInterface
 *
 * Defines the contract for a weather service that can fetch current weather data.
 */
interface WeatherServiceInterface
{
    /**
     * Get the current weather for a specific city.
     *
     * @param string $city The name of the city.
     * @return array<string, mixed>|null The weather data as an associative array, or null if not found or an error occurs.
     */
    public function getCurrentWeather(string $city): ?array;

     /**
     * Search for locations based on a query string.
     *
     * @param string $query The search query (e.g., city name fragment).
     * @return array<int, array<string, mixed>>|null An array of location data, or null on failure or if no results.
     */
    public function searchLocations(string $query): ?array;
}