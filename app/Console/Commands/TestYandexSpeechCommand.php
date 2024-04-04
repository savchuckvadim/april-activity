<?php

namespace App\Console\Commands;

use Aws\Exception\AwsException;
use Aws\S3\S3Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\Algorithm\PS256;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Jose\Component\Core\AlgorithmManagerFactory;




class TestYandexSpeechCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ya:speech';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $iamToken = env('I_AM');
        $iamFolder = env('YA_FOLDER_ID');
        $yaAccessKeyId = env('YA_ACCESS_KEY_KEY_ID');
        $yaAccessSecret = env('YA_ACCESS_KEY_SECRET');

        // $yaYrl = 'https://stt.api.cloud.yandex.net/speech/v1/stt:recognize'; //sync
        $yaYrl = 'https://transcribe.api.cloud.yandex.net/speech/stt/v2/longRunningRecognize'; //async
        $audioUri = 'https://cloudpbx.beeline.ru/api/downloadCallRecording/6ecf0c99f0c7b0795b57b05170b5066/record/f017f526fd75682f052907a0d8a48d1b'; // Путь к вашему аудиофайлу в Yandex Object Storage
        $localFilePath = storage_path('app/public/audio/test_short.mp3');
        $fileUri = 'https://april-test.storage.yandexcloud.net/test/audio.mp3';
        // $s3Client = new S3Client([
        //     'version' => 'latest',
        //     'region'  => 'ru-central1',
        //     'endpoint' => 'https://storage.yandexcloud.net',
        //     'credentials' => [
        //         'key'    => $yaAccessKeyId,
        //         'secret' => $yaAccessSecret,
        //     ],
        // ]);

        // try {
        //     $result = $s3Client->putObject([
        //         'Bucket' => 'april-test',
        //         'Key'    => 'test/audio.mp3',
        //         'SourceFile' => $localFilePath,
        //     ]);
        //     echo "Файл успешно загружен: " . $result['ObjectURL'] . "\n";
        //     $fileUri = $result['ObjectURL'];
        // } catch (AwsException $e) {
        //     echo "Ошибка загрузки файла: " . $e->getMessage() . "\n";
        // }


        $response = Http::withHeaders([
            'Authorization' => "Bearer {$iamToken}",
            'Content-Type' => 'application/json',
        ])->post($yaYrl, [
            'config' => [
                'folderId' => $iamFolder, // Добавление folderId здесь

                'specification' => [
                    'languageCode' => 'ru-RU',
                    'model' => 'general',
                    'profanityFilter' => false,
                    'audioEncoding' => 'MP3',
                    'sampleRateHertz' => 48000,
                    'audioChannelCount' => 1,
                    'rawResults' => false,
                ]
            ],
            'audio' => [
                'uri' => $fileUri,
            ]
        ]);


        if ($response->successful()) {

            $done = false;
            $resultTextRespones = null;
            while (!$done) {
                $responseData = $response->json();
                $this->line($responseData);


                $operation = $response->json();
                $operationId = $operation['id']; // Сохраняем ID операции для последующего запроса результатов
                echo ($operationId);
                $attempt = 1;
                // Цикл опроса для проверки статуса операции
                // while (true) {
                // sleep(10); // Ожидание перед следующим запросом статуса
                echo ($attempt);

                $operationStatusResponse = Http::withHeaders([
                    'Authorization' => "Bearer {$iamToken}",
                ])->get("https://operation.api.cloud.yandex.net/operations/{$operationId}");
                echo ($operationStatusResponse);
                $done = $operationStatusResponse['done'];
               

                if ($operationStatusResponse->successful()) {
                    $operationStatus = $operationStatusResponse->json();
                    if (!$operationStatus['done']) {
                        // Логирование, что операция все еще в процессе
                        Log::channel('console')->info("Попытка {$attempt}: операция {$operationId} все еще выполняется.");
                    } else {
                        if (isset($operationStatus['response']['results'])) {
                            // Операция завершена успешно, обработка результатов
                            Log::channel('console')->info("Операция {$operationId} завершена.");
                        } elseif (isset($operationStatus['error'])) {
                            // Ошибка в процессе выполнения операции
                            Log::channel('console')->error("Ошибка операции: " . $operationStatus['error']['message']);
                        }
                    }
                } else {
                    // Логирование ошибки запроса к API
                    Log::channel('console')->error("Ошибка опроса статуса операции: " . $operationStatusResponse->body());
                }

                $attempt++;
            }
            if ($done) {
                $chunks = $operationStatusResponse['response']['chunks'];

                foreach ($chunks as $chunk) {
                    foreach ($chunk['alternatives'] as $alternative) {
                        // Выводим текст каждого фрагмента разговора
                        echo "Текст: " . $alternative['text'] . "\n";
                        echo "Уверенность: " . $alternative['confidence'] . "\n";
    
                        // Проходим по каждому слову для анализа временных меток
                        foreach ($alternative['words'] as $word) {
                            echo "Слово: " . $word['word'] . ", время начала: " . $word['startTime'] . ", время окончания: " . $word['endTime'] . "\n";
                        }
                    }
                }
            }
           
            // }
        } else {
            $this->error("Ошибка запроса! Статус: " . $response->status());
            $this->line("Ответ: " . $response->body());
        }
    }
}
