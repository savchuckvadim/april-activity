<?php

use App\Http\Controllers\Admin\Garant\ComplectController;
use Illuminate\Support\Facades\Route;


Route::prefix('garant')->group(function () {
   
    Route::get('complects', [ComplectController::class, 'getAll']);
    Route::get('complect/{complectId}', [ComplectController::class, 'get']);
    Route::get('complect/{complectId}/infoblocks', [ComplectController::class, 'infoblocks']);
    Route::get('complect-infoblock/{infoblockId}', [ComplectController::class, 'infoblock']);

});

