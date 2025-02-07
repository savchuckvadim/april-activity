<?php

use App\Http\Controllers\APIController;
use App\Models\Google\GoogleToken;
use Illuminate\Support\Facades\Route;
use Google\Client as GoogleClient;
use Google\Service\Gmail;




Route::get('/fetch-emails', function () {
    $tokenData = GoogleToken::first();  // Получаем токен из БД
    $client = new GoogleClient();
    $client->setClientId(env('GMAIL_CLIENT_ID'));
    $client->setClientSecret(env('GMAIL_SECRET_ID'));
    $client->setRedirectUri(env('GMAIL_REDIRECT'));

    // if ($client->isAccessTokenExpired()) {
    //     $client->fetchAccessTokenWithRefreshToken($refreshToken);
    //     if ($client->getAccessToken()) {
    //         // Успешное обновление токена
    //     } else {
    //         // Ошибка, нужно переавторизоваться
    //     }
    // }


    // Проверяем, истек ли токен
    if (now()->gte($tokenData->expires_at)) {
        $client->refreshToken($tokenData->refresh_token);  // Обновляем токен
        $newToken = $client->getAccessToken();
        $tokenData->update([
            'access_token' => $newToken['access_token'],
            'expires_at' => now()->addSeconds($newToken['expires_in']),
        ]);
    } else {
        $client->setAccessToken($tokenData->access_token);
    }

    // Работа с Gmail API
    $gmailService = new Gmail($client);
    $messages = $gmailService->users_messages->listUsersMessages('me', ['maxResults' => 10]);
    $subjects = [];
    $files = [];
    foreach ($messages->getMessages() as $message) {
        $msg = $gmailService->users_messages->get('me', $message->getId());

        // Извлекаем тему письма
        $subject = collect($msg->getPayload()->getHeaders())
            ->firstWhere('name', 'Subject')
            ->value;

        // Проверка по теме письма
        if (str_contains($subject, 'Отчет СКАП')) {
            array_push($subjects, $subject);

            foreach ($msg->getPayload()->getParts() as $part) {
                if ($part->getFilename() && $part->getBody()->getAttachmentId()) {
                    $attachmentId = $part->getBody()->getAttachmentId();
                    $attachment = $gmailService->users_messages_attachments
                        ->get('me', $message->getId(), $attachmentId);

                    $fileData = base64_decode($attachment->getData());
                    $hash = md5(uniqid(mt_rand(), true));

                    $directoryPath = storage_path('app/public/skap/test/lastreport/' . $hash);

                    // Проверяем, существует ли директория
                    if (!file_exists($directoryPath)) {
                        mkdir($directoryPath, 0775, true);  // Создаём папку с правами 0775
                    }

                    // Полный путь к файлу
                    $filePath = $directoryPath . '/' . $part->getFilename();

                    // $filePath = storage_path('app/public/skap/test/lastreport/' . $hash . '/' . $part->getFilename());
                    file_put_contents($filePath, $fileData);


                    $link =   route('download-skap-report', ['hash' => $hash, 'filename' => $part->getFilename()]);

                    array_push($files, $link);

                    // Здесь отправляем файл дальше по API
                }
            }
        }
    }

    // return 'Письма обработаны!';
    return APIController::getSuccess([
        'subjects' => $subjects,
        'files' => $files,
    ]);
});


Route::get('/download/skap/{hash}/{filename}', function ($hash, $filename) {
    // Декодируем имя файла
    $filename = urldecode($filename);

    // Путь к файлу
    $filePath = storage_path('app/public/skap/test/lastreport/' . $hash . '/' . $filename);

    // Логирование для отладки
    // Log::channel('telegram')->info("Проверка пути к файлу: " . $filePath);

    // Временная проверка
    if (!file_exists($filePath)) {
        // Log::channel('telegram')->info("Файл не найден: " . $filePath);
        return response()->json(['error' => 'Файл не найден', 'path' => $filePath], 404);
    }

    // Скачивание файла
    return response()->download($filePath, $filename);
})->name('download-skap-report');
