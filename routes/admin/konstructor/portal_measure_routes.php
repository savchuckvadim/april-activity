<?php

use App\Http\Controllers\Admin\BitrixfieldController;
use App\Http\Controllers\Admin\BtxCategoryController;
use App\Http\Controllers\Admin\BtxRpaController;
use App\Http\Controllers\Admin\PortalMeasureController;
use App\Models\PortalMeasure;
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
Route::get('initial/portal/{portalId}/measure', [PortalMeasureController::class, 'getInitial']);
// single initial
Route::get('initial/measure',  [PortalMeasureController::class, 'getInitial']);
// .............................................GET  SMART
// all from parent  smart
Route::get('portal/{portalId}/measures', [PortalMeasureController::class, 'getByPortal']);

// ...............  get smart
Route::get('measure/{measureId}', [PortalMeasureController::class, 'get']);


//...............................................SET RPA

Route::post('portal/{portalId}/measure', [PortalMeasureController::class, 'store']);
// Route::post('measure/{measureId}', [PortalMeasure::class, 'store']);

// ............................................DELETE
Route::delete('measure/{measureaId}', [PortalMeasureController::class, 'destroy']);


//FIELDS


// Route::get('initial/measure/{measureId}/bitrixfield', function ($rpaId) {

//     return BitrixfieldController::getInitial($rpaId, 'measure');
// });
// // .............................................GET 

// Route::get('measure/{measureId}/bitrixfields',[BtxRpaController::class, 'getFields']);


//...............................................SET 

// .................. parent - smart
// Route::post('measure/{measureId}/bitrixfield', function (Request $request) {
//     //store = set or uppdate
//     return BitrixfieldController::store($request);
// });


    //...................................................SET  category
    // .................................   set or update
    // ............................from parent smart
    // Route::post('measure/{measureId}/category', function (Request $request) {
    //     //.........set                                                 store = set or uppdate
    //     return BtxCategoryController::store($request);
    // });
    // // ....................................................GET   category

    //................................. .get categories from parent
    //  ........get categories -  all from parent  smart  
    // Route::get('measure/{measureId}/categories', [BtxRpaController::class, 'getCategories']);
