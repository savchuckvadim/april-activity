<?php

use App\Http\Controllers\BitrixInstall\InstallController;
use App\Http\Controllers\BitrixInstall\InstallDealController;
use App\Http\Controllers\BitrixInstall\InstallFieldsController;
use App\Http\Controllers\BitrixInstall\ListController;
use App\Http\Controllers\BitrixInstall\RPA\InstallRPAController;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\PDFDocumentController;
use App\Http\Controllers\PortalInstall\InfoblockInstallController;
use App\Http\Controllers\PortalInstall\TempalteFieldsInstallController;
use App\Models\Link;
use Illuminate\Support\Facades\Log;
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

Route::get('/download/{hash}/{filename}', function ($hash, $filename) {
    // Путь к файлу на основе хэша
    $filePath = storage_path('app/public/projects/alfacontracts/ppk/documents/' . $hash . '/' . $filename);

    // Проверка, существует ли файл
    if (!file_exists($filePath)) {
        return response()->json(['error' => 'Файл не найден'], 404);
    }

    // Отправляем файл для скачивания
    return response()->download($filePath, $filename);
})->name('download-document');






Route::get('/download/report/{domain}/{hash}/{filename}', function ($domain, $hash, $filename) {
    // Декодируем имя файла
    $filename = urldecode($filename);

    // Путь к файлу
    $filePath = storage_path('app/public/clients/' . $domain . '/supplies/' . $hash . '/' . $filename);

    // Логирование для отладки
    // Log::channel('telegram')->info("Проверка пути к файлу: " . $filePath);

    // Временная проверка
    if (!file_exists($filePath)) {
        Log::channel('telegram')->info("Файл не найден: " . $filePath);
        return response()->json(['error' => 'Файл не найден', 'path' => $filePath], 404);
    }

    // Скачивание файла
    return response()->download($filePath, $filename);
})->name('download-supply-report');


Route::get('/file/report/{domain}/{hash}/{filename}', function ($domain, $hash, $filename) {
    // Декодируем имя файла
    $filename = urldecode($filename);

    // Путь к файлу
    $filePath = storage_path('app/public/clients/' . $domain . '/supplies/' . $hash . '/' . $filename);

    // Логирование для отладки
    Log::channel('telegram')->info("Проверка пути к файлу: " . $filePath);

    // Временная проверка
    if (!file_exists($filePath)) {
        Log::channel('telegram')->info("Файл не найден: " . $filePath);
        return response()->json(['error' => 'Файл не найден', 'path' => $filePath], 404);
    }

    // Скачивание файла
    return response()->file($filePath);
})->name('file-supply-report');




//utf
Route::get('/report/{domain}/{hash}/{filename}', function ($domain, $hash, $filename) {
    // Декодируем имя файла
    $filename = urldecode($filename);

    // Путь к файлу
    $filePath = storage_path('app/public/clients/' . $domain . '/supplies/' . $hash . '/' . $filename);

    // Логирование для отладки
    // Log::channel('telegram')->info("Проверка пути к файлу: " . $filePath);

    // Временная проверка
    if (!file_exists($filePath)) {
        Log::channel('telegram')->info("Файл не найден: " . $filePath);
        return response()->json(['error' => 'Файл не найден', 'path' => $filePath], 404);
    }

    $fileContent = file_get_contents($filePath);
    $fileBase64 = base64_encode($fileContent);

    return response()->json([
        'file_base64' => $fileBase64,
        // 'file' => $fileContent,
        'filename' => $filename,
        'mime_type' => mime_content_type($filePath),
    ]);
})->name('supply-report');


















Route::get('/supply/{domain}/{hash}/{filename}', function ($domain, $hash, $filename) {


    // $path = $domain . '/supplies/' . $hash . '/';
    // $filePath = storage_path('app/public/clients/' . $path);
    // // Путь к файлу на основе хэша
    // // $filePath = storage_path('app/public/clients/supply/' . $domain . '/' . $hash . '/' . $filename);

    // // Проверка, существует ли файл
    // if (!file_exists($filePath)) {
    //     return response()->json(['error' => 'Файл не найден'], 404);
    // }

    // Отправляем файл для скачивания
    // return response()->download($filePath, $filename);
})->name('download-supply');

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


// Route::get('/install/fields/{entityType}/{pass}/{domain}/{token}/{smartId}', function (
//     $entityType,
//     $pass,
//     $domain,
//     $token,
//     $smartId = null
// ) {
//     // $url = LinkController ::urlForRedirect($linkId);
//     if ($pass == 'nmbrsdntl') {
//         return InstallFieldsController::setFields($token, $entityType, $domain, $smartId);
//     } else {
//         return 'yo';
//     }
// });


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
    'infoblock/{pass}/{token}',
    function ($pass, $token) {

        // if ($pass == 'nmbrsdntl') {
        //     return InfoblockInstallController::setIblocks($token);
        // } else {
        return 'yo';
        // }
    }
);
