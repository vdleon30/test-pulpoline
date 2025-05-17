<?php

namespace App\Http\Resources\History;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     title="Search History Resource",
 *     description="User's search history item",
 *     @OA\Property(property="id", type="integer", readOnly="true", example=1),
 *     @OA\Property(property="query_term", type="string", example="London", description="The term the user originally searched for"),
 *     @OA\Property(property="location_name", type="string", example="London, City of London, Greater London, United Kingdom", description="The actual location name returned by the weather API"),
 *     @OA\Property(property="weather_data", type="object", description="A snapshot of the weather data returned at the time of search. Structure matches WeatherResource.",
 *          ref="#/components/schemas/WeatherResource"
 *     ),
 *     @OA\Property(property="searched_at", type="string", format="date-time", readOnly="true", example="2023-01-01T12:05:00.000000Z")
 * )
 */
class SearchHistoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'query_term' => $this->query_term,
            'location_name' => $this->location_name,
            'weather_data' => $this->weather_data,
            'searched_at' => $this->created_at->toIso8601String(),
        ];
    }
}