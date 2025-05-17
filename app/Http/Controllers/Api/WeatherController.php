<?php

namespace App\Http\Controllers\Api;

use App\Models\SearchHistory;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Weather\WeatherResource;
use App\Http\Resources\Weather\LocationResource;
use App\Contracts\Weather\WeatherServiceInterface;
use App\Contracts\History\SearchHistoryServiceInterface;
use Illuminate\Http\Request; 

class WeatherController extends Controller
{
    protected WeatherServiceInterface $weatherService;
    protected SearchHistoryServiceInterface $searchHistoryService;

    public function __construct(
        WeatherServiceInterface $weatherService,
        SearchHistoryServiceInterface $searchHistoryService 
    ) {
        $this->weatherService = $weatherService;
        $this->searchHistoryService = $searchHistoryService;
    }

    /**
     * Display the current weather for a given city.
     * @OA\Get(
     *      path="/api/weather/{city}",
     *      operationId="getCurrentWeatherForCity",
     *      tags={"Weather"},
     *      summary="Get current weather for a city",
     *      description="Returns current weather data for the specified city. Also logs the search to user's history.",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(
     *          name="city",
     *          in="path",
     *          description="Name of the city",
     *          required=true,
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(response=200, description="Successful operation", @OA\JsonContent(ref="#/components/schemas/WeatherResource")),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=404, description="City not found or API error"),
     *      @OA\Response(response=422, description="Validation error (e.g., city name too long)")
     * )
     */
    public function show(Request $request, string $city)
    {
        $validator = Validator::make(['city' => $city], [
            'city' => 'required|string|max:100', 
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $user = $request->user();
        $weatherData = $this->weatherService->getCurrentWeather($city);

        if (!$weatherData) {
            return response()->json(['message' => __('Could not retrieve weather data for the specified city or an API error occurred.')], 404);
        }
        $this->searchHistoryService->logSearch($user, $city, $weatherData);
        return new WeatherResource($weatherData);
    }

    /**
     * Search for locations based on a query string.
     * @OA\Get(
     *      path="/api/weather/search",
     *      operationId="searchWeatherLocations",
     *      tags={"Weather"},
     *      summary="Search for weather locations",
     *      description="Returns a list of locations matching the search query.",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(
     *          name="q",
     *          in="query",
     *          description="Search query (e.g., city name fragment)",
     *          required=true,
     *          @OA\Schema(type="string", minLength=2)
     *      ),
     *      @OA\Response(response=200, description="Successful operation", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/LocationResource"))),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=404, description="No locations found or API error"),
     *      @OA\Response(response=422, description="Validation error (e.g., query too short)")
     * )
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'q' => 'required|string|min:2|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $locations = $this->weatherService->searchLocations($request->query('q'));

        if (is_null($locations) || empty($locations)) {
            return response()->json(['message' => __('No locations found for the given query or an API error occurred.')], 404);
        }

        return LocationResource::collection($locations);
    }
}