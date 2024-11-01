<?php

use App\Http\Controllers\AdminOuter\DealController;
use App\Http\Controllers\AdminOuter\FieldsController;
use App\Http\Controllers\AdminOuter\ListController;
use App\Http\Controllers\APIController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

Route::prefix('install')->middleware('check.ip.api_key')->group(function () {

    // Route::prefix('install')->group(function () {
    Route::post('list', [ListController::class, 'setLists']);
    Route::post('deal', [DealController::class, 'setDeals']);
    Route::post('fields', [FieldsController::class, 'setFields']);
    // fields
    // entity_type
    // domain
    // is_rewrite

    Route::post('test', function (Request $request) {
        $data = $request->all();
        Log::channel('telegram')->info('data', ['data' => $data]);

        return APIController::getSuccess($data);
    });
});
