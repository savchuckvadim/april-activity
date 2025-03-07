<?php

use App\Http\Controllers\AdminOuter\DealController;
use App\Http\Controllers\AdminOuter\FieldsController;
use App\Http\Controllers\AdminOuter\ListController;
use App\Http\Controllers\AdminOuter\RPA\RPAController;
use App\Http\Controllers\AdminOuter\SmartController;
use App\Http\Controllers\APIController;
use App\Http\Controllers\BxRqController;
use App\Http\Controllers\Outer\PortalController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

Route::prefix('install')->middleware('check.ip.api_key')->group(function () {

    // Route::prefix('install')->group(function () {
    Route::post('portals', [PortalController::class, 'all']);
    Route::post('bx_rq', [BxRqController::class, 'store']);
    Route::get('bx_rq', [BxRqController::class, 'get']);

    Route::post('list', [ListController::class, 'setLists']);
    Route::post('deal', [DealController::class, 'setDeals']);
    Route::post('fields', [FieldsController::class, 'setFields']);
    Route::post('delete/fields', [FieldsController::class, 'deleteFields']);

    Route::post('rpa', [RPAController::class, 'installRPA']);
    Route::post('smarts', [SmartController::class, 'install']);
    // fields
    // entity_type
    // domain
    // is_rewrite

    // Route::post('get', function (Request $request) {
    //     $data = $request->all();
    //     Log::channel('telegram')->info('data', ['data' => $data]);

    //     return APIController::getSuccess($data);
    // });

    Route::post('test', function (Request $request) {
        $data = $request->all();
        Log::channel('telegram')->info('data', ['data' => $data]);

        return APIController::getSuccess($data);
    });
});
