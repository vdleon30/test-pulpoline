<?php

namespace App\Http\Resources\Favorite;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     title="Favorite City Resource",
 *     description="Favorite city data",
 *     @OA\Property(property="id", type="integer", readOnly="true", example=1),
 *     @OA\Property(property="city_name", type="string", example="Paris"),
 *     @OA\Property(property="added_at", type="string", format="date-time", readOnly="true", example="2023-01-01T12:00:00.000000Z")
 * )
 */
class FavoriteCityResource extends JsonResource
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
            'city_name' => $this->city_name,
            'added_at' => $this->created_at->toIso8601String(),
        ];
    }
}