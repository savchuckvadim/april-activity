<?php

use App\Http\Controllers\AiController;
use Illuminate\Support\Facades\Route;

Route::prefix('ai')->group(function () {
    Route::get('/', [AiController::class, 'index']);
    Route::get('/portal/{portalId}', [AiController::class, 'getByPortal']);
    Route::get('/{id}', [AiController::class, 'show']);
    Route::post('/', [AiController::class, 'store']);
    Route::put('/{id}', [AiController::class, 'update']);
    Route::delete('/{id}', [AiController::class, 'destroy']);
});
