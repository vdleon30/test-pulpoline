<?php

namespace App\Services\Weather;

use App\Contracts\Weather\WeatherServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;


class WeatherService implements WeatherServiceInterface
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.weatherapi.key');
        $this->baseUrl = config('services.weatherapi.base_url');

        if (empty($this->apiKey)) {
            Log::error('WeatherAPI key is not configured.');
        }
    }

    /**
     * Get the current weather for a specific city.
     *
     * @param string $city The name of the city.
     * @return array<string, mixed>|null The weather data, or null on failure.
     */
    public function getCurrentWeather(string $city): ?array
    {

        if (empty($this->apiKey)) {
            return null; 
        }

        $cacheKey = "weather_current_" . App::getLocale() . "_" . \Str::slug($city);
        $cacheDuration = now()->addMinutes(15);

        return Cache::remember($cacheKey, $cacheDuration, function () use ($city) {
            $response = Http::get("{$this->baseUrl}/current.json", [
                'key' => $this->apiKey,
                'q' => $city,
                'aqi' => 'no',
                'lang' => App::getLocale()

            ]);

            if ($response->failed()) {
                Log::error("WeatherAPI request failed for city {$city}: " . $response->status(), [
                    'response_body' => $response->body()
                ]);
                return null;
            }

            $data = $response->json();

            if (!isset($data['location']) || !isset($data['current'])) {
                Log::warning("WeatherAPI returned unexpected data for city {$city}", ['query' => $city, 'response' => $data]);
                return null;
            }

            return [
                'city' => $data['location']['name'] ?? $city,
                'temperature_celsius' => $data['current']['temp_c'] ?? null,
                'condition' => $data['current']['condition']['text'] ?? null,
                'wind_kph' => $data['current']['wind_kph'] ?? null,
                'humidity_percent' => $data['current']['humidity'] ?? null,
                'local_time' => $data['location']['localtime'] ?? null,
            ];
        });
    }

    /**
     * Search for locations based on a query string.
     *
     * @param string $query The search query.
     * @return array<int, array<string, mixed>>|null An array of location data, or null on failure.
     */
    public function searchLocations(string $query): ?array
    {
        if (empty($this->apiKey)) {
            return null; // API key not set
        }

        $response = Http::get("{$this->baseUrl}/search.json", [
            'key' => $this->apiKey,
            'q' => $query,
        ]);

        if ($response->failed()) {
            Log::error("WeatherAPI search request failed for query \"{$query}\": " . $response->status(), [
                'response_body' => $response->body()
            ]);
            return null;
        }

        return $response->json();
    }
}