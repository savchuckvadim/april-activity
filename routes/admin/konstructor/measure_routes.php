<?php

use App\Http\Controllers\Admin\BitrixfieldController;
use App\Http\Controllers\Admin\BtxCategoryController;
use App\Http\Controllers\Admin\BtxRpaController;
use App\Http\Controllers\Admin\MeasureController;
use App\Models\Measure;
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
Route::get('initial/portal/{portalId}/measure', [MeasureController::class, 'getInitial']);
// single initial
Route::get('initial/measure',  [MeasureController::class, 'getInitial']);
// .............................................GET  SMART
// all from parent  smart
Route::get('portal/{portalId}/measures', [MeasureController::class, 'getByPortal']);

// ...............  get smart
Route::get('measure/{measureId}', [MeasureController::class, 'get']);


//...............................................SET RPA

Route::post('portal/{portalId}/measure', [MeasureController::class, 'store']);
Route::post('measure/{measureId}', [MeasureController::class, 'store']);

// ............................................DELETE
Route::delete('measure/{measureaId}', [MeasureController::class, 'destroy']);

