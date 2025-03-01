<?php


use App\Http\Controllers\Front\Konstructor\ContractController;
use App\Http\Controllers\Front\Konstructor\InfoblockFrontController;
use App\Http\Controllers\Front\Konstructor\SupplyController;
use App\Http\Controllers\Yandex\TranscribationController;
use Illuminate\Support\Facades\Route;

Route::prefix('transcription')->group(function () {
   
 
    Route::post( '',[TranscribationController::class, 'getTranscribation']);

});


