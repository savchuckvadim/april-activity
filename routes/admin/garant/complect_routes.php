<?php

use App\Http\Controllers\Admin\Garant\ComplectController;
use Illuminate\Support\Facades\Route;


Route::prefix('')->group(function () {
    Route::get('initial/complect', [ComplectController::class, 'getInitial']);
    Route::get('complects', [ComplectController::class, 'getAll']);
    Route::get('complect/{complectId}', [ComplectController::class, 'get']);
    Route::get('complect/{complectId}/cinfoblocks', [ComplectController::class, 'infoblocks']);
    Route::get('complect/{complectId}/relation', [ComplectController::class, 'initRelations']);
    Route::get('cinfoblock/{infoblockId}', [ComplectController::class, 'infoblock']);
    Route::post('complect/{complectId}/relation', [ComplectController::class, 'storeRelations']);
    Route::post('complect', [ComplectController::class, 'store']);
    // Route::delete('{smartId}', [SmartController::class, 'delete']);
});

