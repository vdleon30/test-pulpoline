<?php

namespace Database\Factories;

use App\Models\SearchHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SearchHistory>
 */
class SearchHistoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SearchHistory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'query_term' => $this->faker->city(),
            'location_name' => $this->faker->city(),
            'weather_data' => ['temp_c' => $this->faker->randomFloat(1, -10, 30), 'condition' => $this->faker->sentence(2)],
        ];
    }
}