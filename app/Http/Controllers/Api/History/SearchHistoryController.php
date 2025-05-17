<?php

namespace App\Http\Controllers\Api\History;

use Illuminate\Http\Request;
use App\Models\SearchHistory;
use App\Http\Controllers\Controller;
use App\Http\Resources\History\SearchHistoryResource;

 /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     * @OA\Get(
     *      path="/api/history",
     *      operationId="getSearchHistory",
     *      tags={"Search History"},
     *      summary="Get search history for the authenticated user",
     *      description="Returns a paginated list of the user's past weather searches.",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(
     *          name="per_page",
     *          in="query",
     *          description="Number of items per page",
     *          required=false,
     *          @OA\Schema(type="integer", default=15)
     *      ),
     *      @OA\Response(response=200, description="Successful operation", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/SearchHistoryResource"))),
     *      @OA\Response(response=401, description="Unauthenticated")
     * )
     */
class SearchHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $history = $user->searchHistories()->paginate(15); // Paginate for better performance
        return SearchHistoryResource::collection($history);
    }
}
