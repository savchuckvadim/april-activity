<?php

use App\Http\Controllers\Front\Konstructor\DealDocumentOptionController;
use App\Http\Controllers\Front\Konstructor\OfferZakupkiSettingsController;
use Illuminate\Support\Facades\Route;

Route::prefix('konstructor/front')->group(function () {


    Route::get(
        '/zakupki/{domain}/{userId}',
        [OfferZakupkiSettingsController::class, 'get']
    );
    Route::post(
        '/zakupki/store',
        [OfferZakupkiSettingsController::class, 'store']
    );
    Route::put(
        '/zakupki/update/default',
        [OfferZakupkiSettingsController::class, 'updateDefault']
    );
    Route::put(
        '/zakupki/update/{id}',
        [OfferZakupkiSettingsController::class, 'update']
    );
   
    Route::delete(
        '/zakupki/delete/{id}',
        [OfferZakupkiSettingsController::class, 'delete']
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
