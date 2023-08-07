<?php

use App\Http\Controllers\LinkController;
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
    $url = LinkController ::urlForRedirect($linkId);

    return redirect($url);
});
