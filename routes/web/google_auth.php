<?php

use App\Models\Google\GoogleToken;
use Illuminate\Support\Facades\Route;
use Google\Client as GoogleClient;
use Carbon\Carbon;


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


// 1. Перенаправление на авторизацию Google
Route::get('/google-auth', function () {
    $client = new GoogleClient();
    $client->setClientId(env('GMAIL_CLIENT_ID'));
    $client->setClientSecret(env('GMAIL_SECRET_ID'));
    $client->setRedirectUri(env('GMAIL_REDIRECT'));
    $client->addScope('https://www.googleapis.com/auth/gmail.readonly');

    // Эти параметры нужны для получения refresh_token
    // $client->setAccessType('offline');
    // $client->setPrompt('consent');

    $authUrl = $client->createAuthUrl();
    return redirect($authUrl);
});

// 2. Обработка callback от Google и получение токенов

//

Route::get('/oauth2callback', function () {
    $client = new GoogleClient();
    $client->setClientId(env('GMAIL_CLIENT_ID'));
    $client->setClientSecret(env('GMAIL_SECRET_ID'));
    $client->setRedirectUri(env('GMAIL_REDIRECT'));

    $code = request('code');
    $token = $client->fetchAccessTokenWithAuthCode($code);

    // Проверка, есть ли refresh_token
    $refreshToken = $token['refresh_token'] ?? null;
    $message = $token['refresh_token'] ? 'with refresh' :  'without refresh';

    GoogleToken::updateOrCreate([], [
        'access_token' => $token['access_token'],
        'refresh_token' => $refreshToken,
        'expires_at' => Carbon::now()->addSeconds($token['expires_in']),
    ]);

    return 'Авторизация успешна! ' . $message;
});
