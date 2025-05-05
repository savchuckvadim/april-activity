<?php

use App\Http\Controllers\Admin\Garant\ProfPriceController;
use Illuminate\Support\Facades\Route;

Route::prefix('')->group(function () {
    Route::get('initial/garant_prof_price', [ProfPriceController::class, 'getInitial']);
    Route::get('garant_prof_prices', [ProfPriceController::class, 'getAll']);
    Route::get('garant_prof_price/{profPriceId}', [ProfPriceController::class, 'get']);
    Route::get('garant_prof_price/{profPriceId}/complects', [ProfPriceController::class, 'complects']);
    Route::get('garant_prof_price/{profPriceId}/garant-packages', [ProfPriceController::class, 'garantPackages']);
    Route::get('garant_prof_price/{profPriceId}/supplies', [ProfPriceController::class, 'supplies']);
    Route::get('garant_prof_price/{profPriceId}/relation', [ProfPriceController::class, 'initRelations']);
    Route::post('garant_prof_price/{profPriceId}/relation', [ProfPriceController::class, 'storeRelations']);
    Route::post('garant_prof_price', [ProfPriceController::class, 'store']);
});
