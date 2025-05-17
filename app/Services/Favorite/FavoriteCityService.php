<?php

namespace App\Services\Favorite;

use App\Contracts\Favorite\FavoriteCityServiceInterface;
use App\Models\FavoriteCity;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class FavoriteCityService implements FavoriteCityServiceInterface
{
    /**
     * Get a paginated list of a user's favorite cities.
     *
     * @param User $user
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getUserFavorites(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $user->favoriteCities()->paginate($perPage);
    }

    /**
     * Add a city to a user's favorites.
     *
     * @param User $user
     * @param string $cityName
     * @return FavoriteCity|null
     */
    public function addFavorite(User $user, string $cityName): ?FavoriteCity
    {
        if ($user->favoriteCities()->where('city_name', $cityName)->exists()) {
            return null; 
        }

        return $user->favoriteCities()->create(['city_name' => $cityName]);
    }

    public function removeFavorite(User $user, string $cityName): bool
    {
        $deletedCount = $user->favoriteCities()->where('city_name', $cityName)->delete();
        return $deletedCount > 0;
    }
}