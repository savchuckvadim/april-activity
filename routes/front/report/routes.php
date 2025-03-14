<?php



use App\Http\Controllers\Front\Konstructor\FavoriteController;
use Illuminate\Support\Facades\Route;

Route::prefix('report')->group(function () {
    require __DIR__ . '/settings_routes.php';

});
