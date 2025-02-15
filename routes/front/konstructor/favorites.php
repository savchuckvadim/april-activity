<?php



use App\Http\Controllers\Front\Konstructor\FavoriteController;
use Illuminate\Support\Facades\Route;

Route::prefix('konstructor/front')->group(function () {


    Route::get(
        '/favorites/{domain}/{userId}',
        [FavoriteController::class, 'getFavorites']
    );
    Route::get(
        '/favorite/{id}',
        [FavoriteController::class, 'get']
    );
    Route::post(
        '/favorite',
        [FavoriteController::class, 'store']
    );
    Route::post(
        '/favorite/name',
        [FavoriteController::class, 'saveName']
    );
    Route::delete(
        '/favorite/{id}',
        [FavoriteController::class, 'delete']
    );

});
