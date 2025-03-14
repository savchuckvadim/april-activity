<?php



use App\Http\Controllers\Front\Report\ReportSettingsController;
use Illuminate\Support\Facades\Route;

Route::prefix('settings')->group(function () {
    Route::post(
        'get/filter',
        [ReportSettingsController::class, 'get']
    );

    Route::post(
        'filter',
        [ReportSettingsController::class, 'store']
    );
});
