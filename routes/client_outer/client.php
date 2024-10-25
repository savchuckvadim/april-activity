<?php

use App\Http\Controllers\APIController;
use App\Http\Controllers\PortalController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::prefix('client')->middleware('check.ip.api_key')->group(function () {

    // Route::post('portal', function (Request $request) {
    //     $domain  = $request->input('domain');
    //     if ($domain == 'b24-683ezu.bitrix24.ru' || $domain == 'april-dev.bitrix24.ru') {
    //         return PortalController::getFrontPortal($domain);
    //     }

    //     return APIController::getError('portal was not found or permissions что-то там', [
    //         'domain' => $domain
    //     ]);
    // });
});
