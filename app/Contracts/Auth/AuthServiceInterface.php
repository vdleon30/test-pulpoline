<?php

namespace App\Contracts\Auth;

use App\Models\User;
use Illuminate\Http\Request;

interface AuthServiceInterface
{
    /**
     * Register a new user and return the user and token.
     *
     * @param array $data
     * @return array{user: User, token: string}
     */
    public function register(array $data): array;

    /**
     * Attempt to log in a user and return the user and token if successful.
     *
     * @param array $credentials
     * @return array{user: User, token: string}|null
     */
    public function login(array $credentials): ?array;

    public function logout(Request $request): void;
}