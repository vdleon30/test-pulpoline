<?php

namespace App\Contracts\Favorite;

use App\Models\User;
use App\Models\FavoriteCity;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Interface FavoriteCityServiceInterface
 *
 * Defines the contract for a service that manages favorite cities for users.
 */
interface FavoriteCityServiceInterface
{
    /**
     * Get a paginated list of a user's favorite cities.
     *
     * @param User $user
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getUserFavorites(User $user, int $perPage = 15): LengthAwarePaginator;

    /**
     * Add a city to a user's favorites.
     *
     * @param User $user
     * @param string $cityName
     * @return FavoriteCity|null Returns the FavoriteCity model if successful, null if already a favorite.
     */
    public function addFavorite(User $user, string $cityName): ?FavoriteCity;

    /**
     * Remove a city from a user's favorites.
     *
     * @param User $user
     * @param string $cityName
     * @return bool True if successful, false otherwise.
     */
    public function removeFavorite(User $user, string $cityName): bool;
}