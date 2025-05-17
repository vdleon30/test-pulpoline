<?php

namespace App\Providers;

use App\Services\Auth\AuthService;
use App\Services\User\UserService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use App\Services\Weather\WeatherService;
use App\Contracts\Auth\AuthServiceInterface;
use App\Contracts\User\UserServiceInterface;
use App\Http\Middleware\SetLocaleFromHeader;
use App\Services\Favorite\FavoriteCityService;
use App\Services\History\SearchHistoryService;
use App\Http\Middleware\EnforceJsonAcceptHeader;
use App\Contracts\Weather\WeatherServiceInterface;
use App\Contracts\Favorite\FavoriteCityServiceInterface;
use App\Contracts\History\SearchHistoryServiceInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(WeatherServiceInterface::class, WeatherService::class);
        $this->app->bind(SearchHistoryServiceInterface::class, SearchHistoryService::class);
        $this->app->bind(FavoriteCityServiceInterface::class, FavoriteCityService::class);
        $this->app->bind(UserServiceInterface::class, UserService::class);

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->mapApiRoutes();
    }


    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware([
                'api',
                SetLocaleFromHeader::class,
                EnforceJsonAcceptHeader::class,
            ])
            ->group(base_path('routes/api.php'));
    }
}
