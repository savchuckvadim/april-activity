<?php

use App\Http\Controllers\BitrixApp\BitrixAppController;
use App\Http\Controllers\BitrixApp\BitrixAppPlacementController;
use App\Http\Controllers\BitrixApp\AppController;

use Illuminate\Support\Facades\Route;



// Route::prefix('bitrix_setup')->middleware('check.ip.api_key')->group(function () {
  
Route::prefix('bitrix-setup')->group(function () {

    Route::post('/app', [BitrixAppController::class, 'storeOrUpdate']);
    Route::post('/check', [BitrixAppController::class, 'chek']);
    Route::post('/placement', [BitrixAppPlacementController::class, 'store']);
    Route::post('/secret', [AppController::class, 'storeOrUpdate']);
    Route::post('/secret/get', [AppController::class, 'getByCode']);

});

Route::prefix('bitrix-app')->group(function () {

    Route::post('/app/get', [BitrixAppController::class, 'get']);
    Route::get('/apps', [BitrixAppController::class, 'all']);

    Route::post('/secret/get', [AppController::class, 'getByCode']);
    Route::post('/apps/portal/get', [BitrixAppController::class, 'getByPortal']);

});