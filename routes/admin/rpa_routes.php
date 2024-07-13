<?php

use App\Http\Controllers\Admin\BitrixfieldController;
use App\Http\Controllers\Admin\BtxRpaController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// Route::prefix('smart')->group(function () {
//     Route::get('initial/portal/{portalId}', [SmartController::class, 'getInitial']);
//     Route::get('initial', [SmartController::class, 'getInitial']);
//     Route::get('{smartId}', [SmartController::class, 'get']);
//     Route::post('{portalId}', [SmartController::class, 'store']);
//     Route::delete('{smartId}', [SmartController::class, 'delete']);
// });



// ......................................................................... SMARTS

//.................................... initial RPA
// initial from parent
Route::get('initial/portal/{portalId}/rpa', [BtxRpaController::class, 'getInitial']);
// single initial
Route::get('initial/rpa',  [BtxRpaController::class, 'getInitial']);
// .............................................GET  SMART
// all from parent  smart
Route::get('portal/{portalId}/rpas', [BtxRpaController::class, 'getByPortal']);

// ...............  get smart
Route::get('rpa/{rpaId}', [BtxRpaController::class, 'get']);


//...............................................SET RPA

Route::post('portal/{portalId}/rpa', [BtxRpaController::class, 'store']);
Route::post('rpa/{rpaId}', [BtxRpaController::class, 'store']);

// ............................................DELETE
Route::delete('rpa/{rpaId}', [BtxRpaController::class, 'destroy']);


//FIELDS


Route::get('initial/rpa/{rpaId}/bitrixfield', function ($rpaId) {

    return BitrixfieldController::getInitial($rpaId, 'rpa');
});
// .............................................GET 

Route::get('rpa/{rpaId}/bitrixfields',[BtxRpaController::class, 'getFields']);


//...............................................SET 

// .................. parent - smart
Route::post('rpa/{rpaId}/bitrixfield', function (Request $request) {
    //store = set or uppdate
    return BitrixfieldController::store($request);
});
