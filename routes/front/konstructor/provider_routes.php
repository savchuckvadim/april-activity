<?php


use App\Http\Controllers\Front\Konstructor\ProviderCurrentController;
use Illuminate\Support\Facades\Route;

Route::prefix('konstructor/front')->group(function () {



    Route::post(
        'get/provider',
        [ProviderCurrentController::class, 'get']
    );

    Route::post(
        'provider',
        [ProviderCurrentController::class, 'store']
    );


});
