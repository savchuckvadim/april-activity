<?php

use App\Http\Controllers\Admin\BitrixfieldController;
use App\Http\Controllers\Admin\SmartController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::prefix('smart')->group(function () {
    Route::get('initial/portal/{portalId}', [SmartController::class, 'getInitial']);
    Route::get('initial', [SmartController::class, 'getInitial']);
    Route::get('{smartId}', [SmartController::class, 'get']);
    Route::post('{portalId}', [SmartController::class, 'store']);
    Route::delete('{smartId}', [SmartController::class, 'delete']);
});



 // ......................................................................... SMARTS

    //.................................... initial SMART
    // initial from parent
    Route::get('initial/portal/{portalId}/smart', function ($portalId) {

        return SmartController::getInitial($portalId);
    });
    // single initial
    Route::get('initial/smart', function () {
        return SmartController::getInitial();
    });




    // .............................................GET  SMART
    // all from parent  smart
    Route::get('portal/{portalId}/smarts', function ($portalId) {

        return SmartController::getByPortal($portalId);
    });
    // ...............  get smart
    Route::get('smart/{smartId}', function ($smartId) {
        return SmartController::get($smartId);
    });


    //...............................................SET SMART

    Route::post('portal/{portalId}/smart', function (Request $request) {

        return SmartController::store($request);
    });

    Route::post('smart/{smartId}', function (Request $request) {
        return SmartController::store($request);
    });

    // ............................................DELETE
    Route::delete('smart/{smartId}', function ($smartId) {
        return SmartController::delete($smartId);
    });


    //FIELDS


    //.................................... initial
    Route::get('initial/smart/{smartId}/bitrixfield', function ($smartId) {

        return BitrixfieldController::getInitial($smartId, 'smart');
    });
 // .............................................GET 

 Route::get('smart/{smartId}/bitrixfields', function ($smartId) {

    return SmartController::getFields($smartId);
});

    //...............................................SET 

      // .................. parent - smart
      Route::post('smart/{smartId}/bitrixfield', function (Request $request) {
        //store = set or uppdate
        return BitrixfieldController::store($request);
    });

    ?>