<?php

namespace App\Services\Yandex;

use App\Http\Controllers\ALogController;
use App\Services\Yandex\AuthService;
use Aws\S3\S3Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;


class TranscribationService
{

    protected string $iamToken;
    protected string $iamFolder;
    protected string $yaS3Bucket;
    protected string $yaS3Endpoint;
    protected S3Client $s3Client;
    


    protected string $taskId;
    protected string $domain;
    protected string $userId;

    public function __construct(
        $taskId,
        $domain,
        $userId,
    )
    {
        $this->taskId = $taskId;
        $this->domain = $domain;
        $this->userId = $userId;
        // Получаем IAM-токен через AuthService
        $auth = new AuthService($taskId);
        $this->iamToken = $auth->getIamToken();
        ALogController::push(
            'transribe job',
            ['transcription iamToken' => $this->iamToken]
        );
        // Загружаем переменные окружения
        $this->iamFolder = env('YA_FOLDER_ID');
        if (empty($this->iamFolder)) {
            // ALogController::push('$this->iamFolder', ['$this->iamFolder' => $this->iamFolder]);
            Redis::set("transcription:{$this->taskId}:status", "error");
            Redis::set("transcription:{$this->taskId}:error", "Проблемы с Яндекс Folder");
            throw new \Exception("YA_FOLDER_ID is not set in .env file");
        }
        $this->yaS3Bucket = env('YA_BUCKET_NAME', 'april-test');
        $this->yaS3Endpoint = 'https://storage.yandexcloud.net';

        // Инициализируем S3 клиент
        $this->s3Client = new S3Client([
            'version' => 'latest',
            'region'  => 'ru-central1',
            'endpoint' => $this->yaS3Endpoint,
            'credentials' => [
                'key'    => env('YA_ACCESS_KEY_KEY_ID'),
                'secret' => env('YA_ACCESS_KEY_SECRET'),
            ],
        ]);
    }

    /**
     * Главный метод: получает аудиофайл, загружает в S3 и запускает транскрибацию.
     */
    public function transcribe($fileUrl, $fileName): ?string
    {
        $fileContent = Http::get($fileUrl)->body();
        if (!$fileContent) {
            Redis::set("transcription:{$this->taskId}:status", "error");
            Redis::set("transcription:{$this->taskId}:error", "Ошибка загрузки файла");
            ALogController::push('Ошибка загрузки файла', ['fileUrl' => $fileUrl]);
            return null;
        }

        // Сохраняем локально перед загрузкой в облако
        $localFilePath = $this->saveLocalFile($fileContent, $fileName);

        // Загружаем в Yandex S3 и получаем URL
        $fileUri = $this->uploadFileToStorage($localFilePath, $fileName);
        if (!$fileUri) {
            return null;
        }
        ALogController::push('Загружаем в Yandex S3 и получаем URL', ['fileUri' => $fileUri]);


        // Отправляем на транскрибацию
        return $this->transcribeAudio($fileUri);
    }

    /**
     * Сохраняет файл в локальное хранилище.
     */
    private function saveLocalFile($fileContent, $fileName): string
    {
        $filePath = "audio/{$this->domain}/{$this->userId}/{$fileName}";
        Storage::disk('public')->put($filePath, $fileContent);

        return storage_path("app/public/{$filePath}");
    }

    /**
     * Загружает файл в Yandex S3 и возвращает его URI.
     */
    private function uploadFileToStorage($localFilePath, $fileName): ?string
    {
        try {
            $result = $this->s3Client->putObject([
                'Bucket' => $this->yaS3Bucket,
                'Key'    => "audio/{$this->domain}/{$this->userId}/{$fileName}",
                'SourceFile' => $localFilePath,
            ]);
            ALogController::push(
                'transribe job',
                ['uploadFileToStorage' => $result]
            );
            return $result['ObjectURL'] ?? null;
        } catch (\Exception $e) {
            Redis::set("transcription:{$this->taskId}:status", "error");
            Redis::set("transcription:{$this->taskId}:error", "Ошибка загрузки в S3");
            ALogController::push('Ошибка загрузки в S3', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Отправляет аудиофайл в Yandex SpeechKit для транскрибации.
     */
    private function transcribeAudio($fileUri): ?string
    {
        $apiUrl = 'https://transcribe.api.cloud.yandex.net/speech/stt/v2/longRunningRecognize';

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->iamToken}",
            'Content-Type' => 'application/json',
        ])->post($apiUrl, [
            'config' => [
                'folderId' => $this->iamFolder,
                'specification' => [
                    'languageCode' => 'ru-RU',
                    'model' => 'general',
                    'profanityFilter' => false,
                    'audioEncoding' => 'MP3',
                    'sampleRateHertz' => 48000,
                    'audioChannelCount' => 1,
                    'rawResults' => false,
                ],
            ],
            'audio' => [
                'uri' => $fileUri,
            ],
        ]);

        if (!$response->successful()) {
            ALogController::push('Ошибка отправки на распознавание', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            Redis::set("transcription:{$this->taskId}:status", "error");
            Redis::set("transcription:{$this->taskId}:error", "Ошибка отправки на распознавание");
            return null;
        }
        // ALogController::push('transcribeAudio', ['response' => $response->json()]);

        $operationId = $response->json()['id'] ?? null;
        ALogController::push('operationId', ['operationId' => $operationId]);

        return $operationId ? $this->getTranscriptionResult($operationId) : null;
    }

    /**
     * Ожидает завершения операции и получает текст транскрибации.
     */
    private function getTranscriptionResult($operationId): ?string
    {
        $apiUrl = "https://operation.api.cloud.yandex.net/operations/{$operationId}";
        $maxAttempts = 20;
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            sleep(2); // Ждем 2 секунды перед повторным запросом

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->iamToken}",
            ])->get($apiUrl);

            if (!$response->successful()) {
                ALogController::push('Ошибка получения результата', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                Redis::set("transcription:{$this->taskId}:status", "error");
                Redis::set("transcription:{$this->taskId}:error", "Ошибка получения результата");
                return null;
            }
            // ALogController::push('получение результата', [
            //     'response' => $response,
              
            // ]);
            // ALogController::push('получение результата', [
            //     'status' => $response->status(),
            //     'body' => $response->body(),
            // ]);
            $operationData = $response->json();
            if (!empty($operationData['done']) && $operationData['done'] === true) {
                return $this->extractTranscriptionText($operationData);
            }

            $attempt++;
        }

        ALogController::push('Превышено время ожидания транскрибации', ['operationId' => $operationId]);
        return null;
    }

    /**
     * Извлекает текст транскрибации из ответа Yandex Cloud.
     */
    private function extractTranscriptionText($operationData): ?string
    {
        if (empty($operationData['response']['chunks'])) {
            ALogController::push('Нет данных в транскрибации', ['response' => $operationData['response']]);
            Redis::set("transcription:{$this->taskId}:status", "error");
            Redis::set("transcription:{$this->taskId}:error", "Нет данных в транскрибации");
            return null;
        }

        $text = '';
        foreach ($operationData['response']['chunks'] as $chunk) {
            foreach ($chunk['alternatives'] as $alternative) {
                $text .= $alternative['text'] . "\n";
            }
        }
        Redis::set("transcription:{$this->taskId}:text", $text);
        Redis::set("transcription:{$this->taskId}:status", "done");

        return trim($text);
    }

}
