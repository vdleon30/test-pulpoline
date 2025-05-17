<?php

namespace Tests\Unit\Services\Weather;

use App\Contracts\Weather\WeatherServiceInterface;
use App\Services\Weather\WeatherService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase; // Optional: if your service interacts with DB directly

class WeatherServiceTest extends TestCase
{
    private WeatherServiceInterface $weatherService;

    protected function setUp(): void
    {
        parent::setUp();
        // Mock config values if they are not set in phpunit.xml or .env.testing
        config(['services.weatherapi.key' => 'test_api_key']);
        config(['services.weatherapi.base_url' => 'http://fakeapi.weather.com/v1']);

        $this->weatherService = new WeatherService();
    }

    /** @test */
    public function it_can_get_current_weather_successfully()
    {
        $cityName = 'London';
        $fakeApiResponse = [
            'location' => [
                'name' => 'London',
                'localtime' => '2023-10-27 10:00',
            ],
            'current' => [
                'temp_c' => 15.0,
                'condition' => ['text' => 'Partly cloudy'],
                'wind_kph' => 10.0,
                'humidity' => 70,
            ],
        ];

        Http::fake([
            config('services.weatherapi.base_url').'/current.json*' => Http::response($fakeApiResponse, 200),
        ]);

        $weatherData = $this->weatherService->getCurrentWeather($cityName);

        $this->assertNotNull($weatherData);
        $this->assertEquals($cityName, $weatherData['city']);
        $this->assertEquals(15.0, $weatherData['temperature_celsius']);
        $this->assertEquals('Partly cloudy', $weatherData['condition']);
    }

    /** @test */
    public function it_returns_null_when_api_key_is_not_configured()
    {
        // Temporarily unset the API key for this test
        $originalApiKey = config('services.weatherapi.key'); // Store original
        config(['services.weatherapi.key' => null]);

        Log::shouldReceive('error')->once()->with('WeatherAPI key is not configured.');

        // Instantiate the service *after* setting the config and log expectation for this test
        $weatherServiceWithoutKey = new WeatherService();

        $weatherData = $weatherServiceWithoutKey->getCurrentWeather('Paris');

        $this->assertNull($weatherData);
        config(['services.weatherapi.key' => $originalApiKey]); // Restore original config
    }

    /** @test */
    public function it_returns_null_and_logs_error_on_api_failure()
    {
        $cityName = 'InvalidCity';

        Http::fake([
            config('services.weatherapi.base_url').'/current.json*' => Http::response(['error' => ['message' => 'City not found']], 400),
        ]);

        Log::shouldReceive('error')
            ->once()
            ->with("WeatherAPI request failed for city {$cityName}: 400", \Mockery::any());

        $weatherData = $this->weatherService->getCurrentWeather($cityName);

        $this->assertNull($weatherData);
    }

     /** @test */
    public function it_returns_null_for_unexpected_api_response_structure()
    {
        $cityName = 'London';
        $fakeApiResponse = ['unexpected_key' => 'unexpected_value']; // Missing 'location' or 'current'

        Http::fake([
            config('services.weatherapi.base_url').'/current.json*' => Http::response($fakeApiResponse, 200),
        ]);

        Log::shouldReceive('warning')->once()->with("WeatherAPI returned unexpected data for city {$cityName}", \Mockery::on(function($data) use ($fakeApiResponse) {
            return $data['query'] === 'London' && $data['response'] === $fakeApiResponse;
        }));

        $weatherData = $this->weatherService->getCurrentWeather($cityName);
        $this->assertNull($weatherData);
    }
}