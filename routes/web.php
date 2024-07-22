<?php

use App\Http\Controllers\BitrixInstall\InstallController;
use App\Http\Controllers\BitrixInstall\InstallDealController;
use App\Http\Controllers\BitrixInstall\InstallFieldsController;
use App\Http\Controllers\BitrixInstall\ListController;
use App\Http\Controllers\BitrixInstall\RPA\InstallRPAController;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\PDFDocumentController;
use App\Http\Controllers\PortalInstall\TempalteFieldsInstallController;
use App\Models\Link;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/link/{linkId}', function ($linkId) {
    $url = LinkController::urlForRedirect($linkId);

    return redirect($url);
});


Route::get('/install/deal/{pass}/{domain}/{token}', function ($pass, $domain, $token) {
    // $url = LinkController ::urlForRedirect($linkId);
    // if ($pass == 'nmbrsdntl') {
    //     return InstallDealController::installDealCtaegories($domain, $token);
    // } else {
    return 'yo';
    // }
});

Route::get('install/smart/{pass}/{token}', function ($pass, $token) {
    // $url = LinkController ::urlForRedirect($linkId);
    // return 'yo';

    // if ($pass == 'nmbrsdntl') {
    //     return InstallController::installSmart($token);
    // } else {
    return 'yo';
    // }
});


Route::get('/install/fields/{entityType}/{pass}/{domain}/{token}/{smartId}', function (
    $entityType,
    $pass,
    $domain,
    $token,
    $smartId = null
) {
    // $url = LinkController ::urlForRedirect($linkId);
    // if ($pass == 'nmbrsdntl') {
    //     return InstallFieldsController::setFields($token, $entityType, $domain, $smartId);
    // } else {
    return 'yo';
    // }
});


Route::get('/install/lists/{pass}/{domain}/{token}/', function ($pass, $domain, $token) {
    // $url = LinkController ::urlForRedirect($linkId);
    // dd([
    //     'pass' => $pass,
    //     'domain' => $domain,
    //     'token' => $token,
    // ]);

    // if ($pass == 'nmbrsdntl' && $domain) {
    //     return ListController::setLists($token, $domain);
    // } else {
    return 'yo';
    // }
});



Route::get('install/rpa/{pass}/{domain}/{token}', function ($pass, $domain, $token) {
    // $url = LinkController ::urlForRedirect($linkId);
    // return 'yo';

    // if ($pass == 'nmbrsdntl') {
    //     return InstallRPAController::installRPA($domain, $token);
    // } else {
    return 'yo';
    // }
});


Route::get(
    'template/{pass}/{templateId}/fields/{token}',
    function ($pass, $templateId, $token) {

        // if ($pass == 'nmbrsdntl') {
        //     return TempalteFieldsInstallController::setFields($templateId, $token);
        // } else {
            return 'yo';
        // }
    }
);


Route::get(
    'infoblock/{pass}/{templateId}/fields/{token}',
    function ($pass, $templateId, $token) {

        // if ($pass == 'nmbrsdntl') {
        //     return TempalteFieldsInstallController::setFields($templateId, $token);
        // } else {
            return 'yo';
        // }
    }
);