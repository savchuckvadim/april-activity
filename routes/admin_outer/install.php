<?php


use App\Http\Controllers\AdminOuter\ListController;
use App\Http\Controllers\APIController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

Route::prefix('install')->middleware('check.ip.api_key')->group(function () {

    // Route::prefix('install')->group(function () {
    // Route::post('list', [ListController::class, 'setLists']);


    Route::post('test', function (Request $request) {
        $data = $request->all();
        Log::channel('telegram')->info('data',['data' => $data]);

        return APIController::getSuccess($data);
    });
});
