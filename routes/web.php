<?php

use App\Http\Controllers\BitrixInstall\InstallController;
use App\Http\Controllers\BitrixInstall\InstallDealController;
use App\Http\Controllers\BitrixInstall\InstallFieldsController;
use App\Http\Controllers\BitrixInstall\ListController;
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

Route::get('install/smart/{pass}/{token}', function ($pass, $token) {
    // $url = LinkController ::urlForRedirect($linkId);
    // return 'yo';

    if ($pass == 'nmbrsdntl') {
        return InstallController::installSmart($token);
    } else {
        return 'yo';
    }
});


Route::get('/install/fields/{entityType}/{pass}/{token}/{smartId}', function ($entityType, $pass, $token, $smartId = null) {
    // $url = LinkController ::urlForRedirect($linkId);
    if ($pass == 'nmbrsdntl') {
        return InstallFieldsController::setFields($token, $entityType, $smartId);
    } else {
        return 'yo';
    }
});


Route::get('/install/lists/{pass}/{domain}/{token}/', function ($pass, $token, $domain) {
    // $url = LinkController ::urlForRedirect($linkId);
    dd($domain);
    if ($pass == 'nmbrsdntl' && $domain) {
        return ListController::setLists($token, $domain);
    } else {
        return 'yo';
    }
});
