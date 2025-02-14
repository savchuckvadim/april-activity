<?php



use App\Http\Controllers\Front\Konstructor\FavoriteController;
use Illuminate\Support\Facades\Route;

Route::prefix('konstructor/front')->group(function () {


    Route::get(
        '/favorites/{domain}/{userId}',
        [FavoriteController::class, 'getFavorites']
    );
    Route::post(
        '/favorite',
        [FavoriteController::class, 'store']
    );

});
