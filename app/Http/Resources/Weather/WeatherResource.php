<?php

namespace App\Http\Resources\Weather;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     title="Weather Resource",
 *     description="Weather data for a specific city",
 *     @OA\Property(property="city", type="string", example="London"),
 *     @OA\Property(property="temperature", type="string", example="15.0 °C"),
 *     @OA\Property(property="condition", type="string", example="Partly cloudy"),
 *     @OA\Property(property="wind", type="string", example="10.0 kph"),
 *     @OA\Property(property="humidity", type="string", example="70%"),
 *     @OA\Property(property="local_time", type="string", example="2023-10-27 10:00"),
 *     @OA\Property(property="retrieved_at", type="string", format="date-time", example="2023-10-27T10:05:00.000000Z")
 * )
 */
class WeatherResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'city' => $this['city'] ?? 'N/A',
            'temperature' => isset($this['temperature_celsius']) ? $this['temperature_celsius'] . ' °C' : 'N/A',
            'condition' => $this['condition'] ?? 'N/A',
            'wind' => isset($this['wind_kph']) ? $this['wind_kph'] . ' kph' : 'N/A',
            'humidity' => isset($this['humidity_percent']) ? $this['humidity_percent'] . '%' : 'N/A',
            'local_time' => $this['local_time'] ?? 'N/A',
            'retrieved_at' => now()->toIso8601String(), // Optional: to know when data was fetched by your API
        ];
    }
}
