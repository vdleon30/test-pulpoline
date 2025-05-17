<?php

namespace App\Contracts\User;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserServiceInterface
{
    /**
     * Get a paginated list of users.
     *
     * @param array $filters Filters to apply (e.g., search term).
     * @param int $perPage Number of items per page.
     * @return LengthAwarePaginator
     */
    public function getAllUsers(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Create a new user.
     *
     * @param array $data User data.
     * @return User The created user.
     */
    public function createUser(array $data): User;

    /**
     * Get a user by their ID.
     *
     * @param int $userId
     * @return User|null
     */
    public function getUserById(int $userId): ?User;

    /**
     * Update an existing user.
     *
     * @param User $user The user model to update.
     * @param array $data Data to update.
     * @return User The updated user.
     */
    public function updateUser(User $user, array $data): User;

    /**
     * Delete a user.
     *
     * @param User $user The user model to delete.
     * @return bool True on success, false otherwise.
     */
    public function deleteUser(User $user): bool;
}