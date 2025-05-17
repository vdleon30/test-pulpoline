<?php

namespace App\Http\Controllers\Api\User;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserResource;
use App\Contracts\User\UserServiceInterface;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;

class UserController extends Controller
{
    protected UserServiceInterface $userService;

    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of the resource.
     * @OA\Get(
     *      path="/api/admin/users",
     *      operationId="getUsersList",
     *      tags={"User Management"},
     *      summary="Get list of users",
     *      description="Returns list of users (paginated). Requires 'manage users' permission.",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(
     *          name="search",
     *          in="query",
     *          description="Search term for name or email",
     *          required=false,
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *          name="per_page",
     *          in="query",
     *          description="Number of items per page",
     *          required=false,
     *          @OA\Schema(type="integer", default=15)
     *      ),
     *      @OA\Response(response=200, description="Successful operation", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/UserResource"))),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=403, description="Forbidden (User does not have the right permissions.)")
     * )
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search']);
        $users = $this->userService->getAllUsers($filters, $request->input('per_page', 15));
        return UserResource::collection($users);
    }

    /**
     * Store a newly created resource in storage.
     * @OA\Post(
     *      path="/api/admin/users",
     *      operationId="storeUser",
     *      tags={"User Management"},
     *      summary="Create new user",
     *      description="Creates a new user. Requires 'manage users' permission.",
     *      security={{"sanctum":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          description="User object that needs to be added",
     *          @OA\JsonContent(ref="#/components/schemas/StoreUserRequest")
     *      ),
     *      @OA\Response(response=201, description="User created successfully", @OA\JsonContent(ref="#/components/schemas/UserResource")),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(StoreUserRequest $request)
    {
        $user = $this->userService->createUser($request->validated());
        return new UserResource($user);
    }

    /**
     * Display the specified resource.
     * @OA\Get(
     *      path="/api/admin/users/{user}",
     *      operationId="getUserById",
     *      tags={"User Management"},
     *      summary="Get user information",
     *      description="Returns user data. Admin can view any user, regular user can view their own.",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(name="user", in="path", description="ID of user to return", required=true, @OA\Schema(type="integer")),
     *      @OA\Response(response=200, description="Successful operation", @OA\JsonContent(ref="#/components/schemas/UserResource")),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=404, description="User not found")
     * )
     */
    public function show(User $user) // Route model binding
    {
        if (request()->user()->id !== $user->id && !request()->user()->can('manage users')) {
            abort(403, 'This action is unauthorized.');
        }
        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     * @OA\Put(
     *      path="/api/admin/users/{user}",
     *      operationId="updateUser",
     *      tags={"User Management"},
     *      summary="Update existing user",
     *      description="Updates an existing user. Admin can update any user, regular user can update their own. Requires 'manage users' permission for admin updates.",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(name="user", in="path", description="ID of user to update", required=true, @OA\Schema(type="integer")),
     *      @OA\RequestBody(
     *          required=true,
     *          description="User object that needs to be updated",
     *          @OA\JsonContent(ref="#/components/schemas/UpdateUserRequest")
     *      ),
     *      @OA\Response(response=200, description="User updated successfully", @OA\JsonContent(ref="#/components/schemas/UserResource")),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=404, description="User not found"),
     *      @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(UpdateUserRequest $request, User $user) // Route model binding
    {
        // Authorization is handled by UpdateUserRequest
        $updatedUser = $this->userService->updateUser($user, $request->validated());
        return new UserResource($updatedUser);
    }

    /**
     * Remove the specified resource from storage.
     * @OA\Delete(
     *      path="/api/admin/users/{user}",
     *      operationId="deleteUser",
     *      tags={"User Management"},
     *      summary="Delete existing user",
     *      description="Deletes an existing user. Requires 'manage users' permission.",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(name="user", in="path", description="ID of user to delete", required=true, @OA\Schema(type="integer")),
     *      @OA\Response(response=200, description="User deleted successfully", @OA\JsonContent(@OA\Property(property="message", type="string", example="User deleted successfully."))),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=403, description="Forbidden (e.g., admin trying to delete self, or no permission)"),
     *      @OA\Response(response=404, description="User not found")
     * )
     */
    public function destroy(User $user) // Route model binding
    {
        // Prevent admin from deleting themselves (optional safeguard)
        if ($user->id === request()->user()->id && $user->hasRole('admin')) {
            return response()->json(['message' => 'Admin user cannot delete themselves.'], 403);
        }

        $this->userService->deleteUser($user);
        return response()->json(['message' => 'User deleted successfully.'], 200);
    }
}