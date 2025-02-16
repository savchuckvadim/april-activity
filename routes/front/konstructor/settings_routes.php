<?php

use App\Http\Controllers\Front\Konstructor\DealDocumentOptionController;
use Illuminate\Support\Facades\Route;

Route::prefix('konstructor/front')->group(function () {


    // Route::get(
    //     '/favorites/{domain}/{userId}',
    //     [FavoriteController::class, 'getFavorites']
    // );
    // Route::get(
    //     '/favorite/{id}',
    //     [FavoriteController::class, 'get']
    // );
    // Route::post(
    //     '/favorite',
    //     [FavoriteController::class, 'store']
    // );
    Route::post(
        'offer/price_options',
        [DealDocumentOptionController::class, 'priceOptionstore']
    );
    Route::post(
        'offer/iblock_options',
        [DealDocumentOptionController::class, 'infoblockOptionstore']
    );
    Route::post(
        'offer/get/options',
        [DealDocumentOptionController::class, 'getOptions']
    );
    // Route::delete(
    //     '/favorite/{id}',
    //     [FavoriteController::class, 'delete']
    // );

});
