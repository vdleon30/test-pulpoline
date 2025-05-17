<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\WeatherController;
use App\Http\Controllers\Api\User\UserController;
use App\Http\Controllers\Api\Favorite\FavoriteCityController;
use App\Http\Controllers\Api\History\SearchHistoryController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
| These routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::prefix('weather')->group(function () {
        Route::get('/search', [WeatherController::class, 'search']);
        Route::get('/{city}', [WeatherController::class, 'show'])->name('weather.show');
    });

    Route::get('/history', [SearchHistoryController::class, 'index'])->name('history.index');

    Route::prefix('favorites')->group(function () {
        Route::get('', [FavoriteCityController::class, 'index'])->name('favorites.index');
        Route::post('', [FavoriteCityController::class, 'store'])->name('favorites.store');
        Route::delete('/{city_name}', [FavoriteCityController::class, 'destroy'])->name('favorites.destroy');
    });

    Route::prefix('admin')->group(function () {
        Route::apiResource('users', UserController::class)->middleware('permission:manage users');
    });
});

