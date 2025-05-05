<?php

use App\Http\Controllers\Admin\Garant\PackageController;
use Illuminate\Support\Facades\Route;

Route::prefix('')->group(function () {
    Route::get('initial/package', [PackageController::class, 'getInitial']);
    Route::get('packages', [PackageController::class, 'getAll']);
    Route::get('package/{packageId}', [PackageController::class, 'get']);
    Route::get('package/{packageId}/pinfoblocks', [PackageController::class, 'infoblocks']);
    Route::get('package/{packageId}/pinfoGroups', [PackageController::class, 'infoGroups']);
    Route::get('package/{packageId}/relation', [PackageController::class, 'initRelations']);
    Route::post('package/{packageId}/relation', [PackageController::class, 'storeRelations']);
    Route::post('package', [PackageController::class, 'store']);
});
