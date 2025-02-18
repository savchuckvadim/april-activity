<?php

use App\Http\Controllers\Front\Konstructor\DealDocumentOptionController;
use App\Http\Controllers\Front\Konstructor\OfferZakupkiSettingsController;
use Illuminate\Support\Facades\Route;

Route::prefix('konstructor/front')->group(function () {


    Route::get(
        '/zakupki/{domain}/{userId}',
        [OfferZakupkiSettingsController::class, 'get']
    );
    // Route::post(
    //     'offer/options',
    //     [OfferZakupkiSettingsController::class, 'store']
    // );
 
    // Route::post(
    //     'offer/get/options',
    //     [OfferZakupkiSettingsController::class, 'getOptions']
    // );
  

});
