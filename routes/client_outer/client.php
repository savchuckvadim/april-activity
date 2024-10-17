<?php


use App\Http\Controllers\PortalController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// Route::prefix('client')->middleware('check.ip.api_key')->group(function () {
    Route::prefix('client')->group(function () {

    Route::post('portal', function (Request $request) {
        $domain  = $request->input('domain');
        return PortalController::getFrontPortal($domain);
    });
});
