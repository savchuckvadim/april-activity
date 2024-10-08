<?php


use App\Http\Controllers\Front\Konstructor\ContractController;
use App\Http\Controllers\Front\Konstructor\SupplyController;
use Illuminate\Support\Facades\Route;

Route::prefix('konstruct')->group(function () {
   
    Route::prefix('contract')->group(function () {
        Route::post('init', [ContractController::class, 'frontInit']);
        Route::post('/', [ContractController::class, 'getDocument']);


        
    });

    Route::prefix('contracts')->group(function () {
   
        Route::post('/', [ContractController::class, 'getByPortal']);
    
    });


    Route::prefix('supply')->group(function () {
        Route::post('init', [SupplyController::class, 'frontInit']);
        Route::post('/', [SupplyController::class, 'getDocument']);


        
    });
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



