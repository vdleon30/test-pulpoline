<?php

namespace Tests\Features\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WeatherApiTest extends TestCase
{
    use RefreshDatabase; // Resets the database for each test

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();

        // Mock config values for WeatherService dependency
        config(['services.weatherapi.key' => 'test_api_key']);
        config(['services.weatherapi.base_url' => 'http://fakeapi.weather.com/v1']);
    }

    /** @test */
    public function authenticated_user_can_get_weather_for_a_valid_city()
    {
        Sanctum::actingAs($this->user);

        $cityName = 'London';
        $fakeApiResponse = [
            'location' => ['name' => 'London', 'localtime' => '2023-10-27 10:00'],
            'current' => [
                'temp_c' => 15.0,
                'condition' => ['text' => 'Sunny'],
                'wind_kph' => 5.0,
                'humidity' => 60,
            ],
        ];

        Http::fake([
            config('services.weatherapi.base_url').'/current.json*' => Http::response($fakeApiResponse, 200),
        ]);

        $response = $this->getJson(route('weather.show', ['city' => $cityName]));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [ // Assuming WeatherResource wraps data under 'data' key
                    'city',
                    'temperature',
                    'condition',
                    'wind',
                    'humidity',
                    'local_time',
                    'retrieved_at'
                ]
            ])
            ->assertJsonPath('data.city', 'London')
            ->assertJsonPath('data.temperature', '15 °C');
    }

    /** @test */
    public function unauthenticated_user_cannot_get_weather()
    {
        $response = $this->getJson(route('weather.show', ['city' => 'London']));

        $response->assertStatus(401) // Unauthenticated
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    /** @test */
    public function it_returns_404_if_weather_data_cannot_be_retrieved_for_city()
    {
        Sanctum::actingAs($this->user);

        $cityName = 'InvalidCityName';

        Http::fake([
            config('services.weatherapi.base_url').'/current.json*' => Http::response(null, 404), // Simulate API not finding city
        ]);

        $response = $this->getJson(route('weather.show', ['city' => $cityName]));

        $response->assertStatus(404)
            ->assertJson(['message' => 'Could not retrieve weather data for the specified city or an API error occurred.']);
    }

    /** @test */
    public function it_returns_validation_error_for_missing_city_parameter_if_route_allowed_it_or_invalid_city_name()
    {
        Sanctum::actingAs($this->user);

        // Test with a very long city name to trigger validation if you have one
        $longCityName = str_repeat('a', 200);
        $response = $this->getJson(route('weather.show', ['city' => $longCityName]));

        // The WeatherController has a basic validator: 'city' => 'required|string|max:100'
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['city']);
    }
}