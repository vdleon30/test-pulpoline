<?php

namespace App\Http\Controllers\Api\Favorite;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Favorite\FavoriteCityResource;
use App\Contracts\Favorite\FavoriteCityServiceInterface; // Import the new interface

class FavoriteCityController extends Controller
{
    protected FavoriteCityServiceInterface $favoriteCityService;

    public function __construct(FavoriteCityServiceInterface $favoriteCityService)
    {
        $this->favoriteCityService = $favoriteCityService;
    }
    /**
     * Display a listing of the resource.
     * @OA\Get(
     *      path="/api/favorites",
     *      operationId="getFavoriteCities",
     *      tags={"Favorites"},
     *      summary="Get list of favorite cities for the authenticated user",
     *      description="Returns a paginated list of the user's favorite cities.",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(
     *          name="per_page",
     *          in="query",
     *          description="Number of items per page",
     *          required=false,
     *          @OA\Schema(type="integer", default=15)
     *      ),
     *      @OA\Response(response=200, description="Successful operation", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/FavoriteCityResource"))),
     *      @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $favorites = $this->favoriteCityService->getUserFavorites($user);
        return FavoriteCityResource::collection($favorites);
    }

    /**
     * Store a newly created resource in storage.
     * @OA\Post(
     *      path="/api/favorites",
     *      operationId="addFavoriteCity",
     *      tags={"Favorites"},
     *      summary="Add a city to favorites",
     *      description="Adds a specified city to the authenticated user's list of favorites.",
     *      security={{"sanctum":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          description="City name to add to favorites",
     *          @OA\JsonContent(
     *              required={"city_name"},
     *              @OA\Property(property="city_name", type="string", example="London")
     *          )
     *      ),
     *      @OA\Response(response=201, description="City added successfully", @OA\JsonContent(ref="#/components/schemas/FavoriteCityResource")),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=409, description="Conflict - City already in favorites"),
     *      @OA\Response(response=422, description="Validation error (e.g., city_name missing or invalid)")
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'city_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        /** @var \App\Models\User $user */
        $user = $request->user();
        $favorite = $this->favoriteCityService->addFavorite($user, $request->city_name);

        if (!$favorite) {
            return response()->json(['message' => __('City already in favorites.')], 409); // 409 Conflict
        }

         return (new FavoriteCityResource($favorite))
                ->response()
                ->setStatusCode(201); 
    }
  

    /**
     * Remove the specified resource from storage.
     * @OA\Delete(
     *      path="/api/favorites/{city_name}",
     *      operationId="removeFavoriteCity",
     *      tags={"Favorites"},
     *      summary="Remove a city from favorites",
     *      description="Removes a specified city from the authenticated user's list of favorites.",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(
     *          name="city_name",
     *          in="path",
     *          description="Name of the city to remove",
     *          required=true,
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(response=200, description="City removed successfully", @OA\JsonContent(@OA\Property(property="message", type="string", example="City removed from favorites."))),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=404, description="City not found in favorites")
     * )
      */
    public function destroy(Request $request, string $city_name) // Using city_name for deletion
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $deleted = $this->favoriteCityService->removeFavorite($user, $city_name);

        return $deleted
            ? response()->json(['message' => __('City removed from favorites.')], 200)
            : response()->json(['message' => __('City not found in favorites.')], 404);
    }
}
