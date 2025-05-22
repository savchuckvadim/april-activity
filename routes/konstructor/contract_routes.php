<?php

use App\Http\Controllers\Admin\ContractController;
use Illuminate\Support\Facades\Route;


Route::prefix('contract')->group(function () {
   
    Route::get('{contractId}', [ContractController::class, 'get']);
    Route::post('', [ContractController::class, 'store']);
    Route::post('{contractId}', [ContractController::class, 'store']);
    Route::delete('{contractId}', [ContractController::class, 'delete']);
});

Route::prefix('contracts')->group(function () {
   
    Route::get('/', [ContractController::class, 'getAll']);

});


// ......................................................................... SMARTS

//.................................... initial RPA
// initial from parent
// Route::get('initial/portal/{portalId}/measure', [MeasureController::class, 'getInitial']);
// single initial
Route::get('initial/contract',  [ContractController::class, 'getInitial']);
// .............................................GET  SMART
// all from parent  smart
// Route::get('measures', [MeasureController::class, 'getAll']);

// ...............  get smart
// Route::get('measure/{measureId}', [MeasureController::class, 'get']);


//...............................................SET RPA

// Route::post('measure', [MeasureController::class, 'store']);
// Route::post('measure/{measureId}', [MeasureController::class, 'store']);

// ............................................DELETE
// Route::delete('measure/{measureaId}', [MeasureController::class, 'destroy']);

