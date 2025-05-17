<?php

namespace App\Http\Resources\Weather;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     title="Location Resource",
 *     description="Location data from weather API search",
 *     @OA\Property(property="id", type="integer", readOnly="true", example=2801268, description="Unique ID of the location from the weather API"),
 *     @OA\Property(property="name", type="string", example="London, City of London, Greater London, United Kingdom", description="Full name of the location"),
 *     @OA\Property(property="region", type="string", example="City of London, Greater London", description="Region of the location"),
 *     @OA\Property(property="country", type="string", example="United Kingdom", description="Country of the location"),
 *     @OA\Property(property="latitude", type="number", format="float", example=51.52, description="Latitude of the location"),
 *     @OA\Property(property="longitude", type="number", format="float", example=-0.11, description="Longitude of the location")
 * )
 */
class LocationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this['id'] ?? null,
            'name' => $this['name'] ?? 'N/A',
            'region' => $this['region'] ?? 'N/A',
            'country' => $this['country'] ?? 'N/A',
            'latitude' => $this['lat'] ?? null,
            'longitude' => $this['lon'] ?? null,
        ];
    }
}