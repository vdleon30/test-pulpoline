<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserResource;
use App\Contracts\Auth\AuthServiceInterface;
use App\Http\Requests\Auth\LoginUserRequest;
use App\Http\Requests\Auth\RegisterUserRequest;

/**
     * @OA\Post(
     *      path="/api/register",
     *      operationId="registerUser",
     *      tags={"Authentication"},
     *      summary="Register a new user",
     *      description="Registers a new user and returns an API token.",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/RegisterUserRequest")
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="User registered successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="User registered successfully"),
     *              @OA\Property(property="access_token", type="string", example="1|abcdef123456"),
     *              @OA\Property(property="token_type", type="string", example="Bearer"),
     *              @OA\Property(property="user", ref="#/components/schemas/UserResource")
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="The given data was invalid."),
     *              @OA\Property(property="errors", type="object")
     *          )
     *      )
     * )
     */
class AuthController extends Controller
{
    /**
     * Register a new user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */

    protected AuthServiceInterface $authService;

    public function __construct(AuthServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    public function register(RegisterUserRequest $request)
    {
        $result = $this->authService->register($request->validated());

        return response()->json([
            'message' => __('User registered successfully'),
            'access_token' => $result['token'],
            'token_type' => 'Bearer',
            'user' => new UserResource($result['user'])
        ], 201);
    }
   /**
     * @OA\Post(
     *      path="/api/login",
     *      operationId="loginUser",
     *      tags={"Authentication"},
     *      summary="Login an existing user",
     *      description="Logs in an existing user and returns an API token.",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/LoginUserRequest")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="User logged in successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="User logged in successfully"),
     *              @OA\Property(property="access_token", type="string", example="2|abcdef123456"),
     *              @OA\Property(property="token_type", type="string", example="Bearer"),
     *              @OA\Property(property="user", ref="#/components/schemas/UserResource")
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Invalid login details",
     *          @OA\JsonContent(@OA\Property(property="message", type="string", example="Invalid login details"))
     *      ),
     *      @OA\Response(response=422, description="Validation error")
     * )
     */
    public function login(LoginUserRequest $request)
    {
        $result = $this->authService->login($request->validated());
        if (!$result) {
            return response()->json(['message' => __('Invalid login details')], 401);
        }

        return response()->json([
            'message' => __('User logged in successfully'),
            'access_token' => $result['token'],
            'token_type' => 'Bearer',
            'user' => new UserResource($result['user'])
        ]);
    }

     /**
     * @OA\Post(
     *      path="/api/logout",
     *      operationId="logoutUser",
     *      tags={"Authentication"},
     *      summary="Logout the current user",
     *      description="Invalidates the current user's API token.",
     *      security={{"sanctum":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Successfully logged out",
     *          @OA\JsonContent(@OA\Property(property="message", type="string", example="Successfully logged out"))
     *      ),
     *      @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function logout(Request $request)
    {
        $this->authService->logout($request);
        return response()->json(['message' => __('Successfully logged out')]);
    }
}