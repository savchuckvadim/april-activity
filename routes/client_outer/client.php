<?php

use App\Http\Controllers\APIController;
use App\Http\Controllers\Outer\PortalController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;


// todo replace to client - it is test without api key


Route::prefix('client')->middleware('check.ip.api_key')->group(function () {
    require __DIR__ . '/gmail/google_mail.php';

    Route::post('portal', function (Request $request) {
        $domain  = $request->input('domain');
        // if ($domain == 'b24-683ezu.bitrix24.ru' || $domain == 'april-dev.bitrix24.ru' || $domain == 'april-garant.bitrix24.ru' || $domain ==  'b24-20qnxm.bitrix24.ru' ||  $domain ==  'b24-riqdvu.bitrix24.ru' ) {
            return PortalController::get($domain);
        // }

        return APIController::getError('portal was not found or permissions что-то там', [
            'domain' => $domain
        ]);
    });
});
