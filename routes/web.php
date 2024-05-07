<?php

use App\Http\Controllers\BitrixInstall\InstallController;
use App\Http\Controllers\BitrixInstall\InstallDealController;
use App\Http\Controllers\BitrixInstall\InstallFieldsController;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\PDFDocumentController;
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


Route::get('/install/deal/{pass}/{token}', function ($pass, $token) {
    // $url = LinkController ::urlForRedirect($linkId);
    if ($pass == 'nmbrsdntl') {
        return InstallDealController::installDealCtaegories($token);
    } else {
        return 'yo';
    }
});

Route::get('install/smart/{token}', function ($token) {
    // $url = LinkController ::urlForRedirect($linkId);

    return InstallController::installSmart($token);
});


Route::get('/install/fields/{token}/', function ($token) {
    // $url = LinkController ::urlForRedirect($linkId);
    return 'yo';
    return InstallFieldsController::setFields($token);
});
