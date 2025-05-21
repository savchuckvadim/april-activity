<?php

use App\Http\Controllers\TranscriptionController;
use Illuminate\Support\Facades\Route;

Route::prefix('transcription')->group(function () {
    // Get all transcriptions (with optional portal_id filter)
    Route::get('/', [TranscriptionController::class, 'index']);

    // Get transcriptions by specific portal
    Route::get('/portal/{portalId}', [TranscriptionController::class, 'getByPortal']);

    // Get specific transcription
    Route::get('/{id}', [TranscriptionController::class, 'show']);

    // Create new transcription
    Route::post('/', [TranscriptionController::class, 'store']);

    // Update transcription
    Route::put('/{id}', [TranscriptionController::class, 'update']);

    // Delete transcription
    Route::delete('/{id}', [TranscriptionController::class, 'destroy']);
});
