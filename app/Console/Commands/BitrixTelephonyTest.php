<?php

namespace App\Console\Commands;

use App\Http\Controllers\BitrixController;
use Aws\Exception\AwsException;
use Aws\S3\S3Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class BitrixTelephonyTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'btx:activity';

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
        $domain = 'april-garant.bitrix24.ru';
        // $method = '/crm.activity.list.json';
        $method = '/crm.activity.list';
        // $hook = env('TEST_HOOK');
        $hook = BitrixController::getHook($domain);
        $dealId = 12202;
        $fields =
            [
                'OWNER_TYPE_ID' => 2, // 2- deal 3 - company
                'OWNER_ID' => 20424, // 2976,
                "TYPE_ID" => 2 // Тип активности - Звонок
            ];

        $url = $hook . $method;
        $data = [
            // "ownerTypeId" => 2,
            // "ownerId" => $dealId,
            // 'id' => 645286,
            'filter' => $fields,
            'order' => ['ID' => 'DESC'],


        ];

        $response = Http::get($url, $data);
        $responseData = $response->json();
        // $this->line(json_encode($responseData));

        if (!empty($responseData['result'])) {
            // $this->line(json_encode($responseData));
            foreach ($responseData['result'] as $key => $activity) { //for list
                $this->line($key);
                $this->line(json_encode($activity['ID']));
            }
        }
        if (!empty($responseData['result'])) {
            // $this->line(json_encode($responseData));
            foreach ($responseData['result'] as $activity) { //for list
                $this->line(json_encode($activity['ID']));
                if (isset($activity['FILES'])) { //for list

                    foreach ($activity['FILES'] as  $file) {
                        $fileId = $file['id'];
                        $fileUrl = $file['url'];
                        $this->line(json_encode('file'));
                        // $this->line(json_encode($file));
                        $this->line(json_encode($fileId));
                        $this->line(json_encode('url'));
                        $this->line(json_encode($fileUrl));
                        $getFileMethod = '/disk.file.get';
                        $url = $hook . $getFileMethod;
                        $responseFile = Http::get($url, [
                            'id' => $fileId
                        ]);
                        if ($responseFile['result']) {
                            $downloadUrl = $responseFile['result']['DOWNLOAD_URL'];



                            // $this->getYascribation($downloadUrl);
                            // Получаем содержимое файла
                            $fileResponse = Http::get($downloadUrl);

                            // Сохраняем файл в storage/app/public/записи
                            $filename = 'audio/' . $responseFile['result']['NAME'];
                            // $filename = 'audio/' . "file.mp3";

                            Storage::disk('public')->put($filename, $fileResponse->body());
                            $localFilePath = storage_path('app/public/' . $filename);
                            $yaFileUri = $this->putYaFile($localFilePath, $filename);
                            $this->line('yaFileUri: ' . $yaFileUri);

                            if ($yaFileUri) {
                                $this->getYascribation($yaFileUri);
                            }
                            // Выводим путь к файлу
                            $this->line('yaFileUri: ' . $yaFileUri);
                        }
                        // $this->line(json_encode('responseFile result'));
                        // $this->line(json_encode($responseFile['result']));
                        // Здесь ваш код для обработки URL файла

                        // $hook = BitrixController::getHook($domain); // Это твой вебхук
                        // $fileUrlWithHook = $fileUrl . '&' . parse_url($hook, PHP_URL_QUERY);
                        // $response = Http::withOptions(['allow_redirects' => true])->get($fileUrlWithHook);

                        // $this->line("Status Code: " . $responseFile->status());
                        // $this->line("Headers: " . json_encode($responseFile->headers()));
                        // $this->line("Body: " . substr($responseFile->body(), 0, 500));


                        // $fileContent = Http::get($fileUrl);
                        // if ($fileContent->successful()) {
                        //     //     // Замените 'path/to/your/directory/' на путь к каталогу, где вы хотите сохранить файлы
                        //     $fileName = $fileId;
                        //     $path = storage_path('app' . DIRECTORY_SEPARATOR . 'audio' . DIRECTORY_SEPARATOR . $domain . DIRECTORY_SEPARATOR . $fileName);
                        //     $directoryPath = dirname($path);
                        //     if (!file_exists($directoryPath)) {
                        //         mkdir($directoryPath, 0777, true); // Рекурсивное создание директорий
                        //     }
                        //     //     // Открытие файла для записи в бинарном режиме
                        //     $fileHandle = fopen($path, 'wb');
                        //     if ($fileHandle === false) {
                        //         // Обработка ошибки открытия файла
                        //         $this->line("Unable to open file for writing: " . $path);
                        //         return;
                        //     }

                        //     // Запись содержимого в файл
                        //     fwrite($fileHandle, $fileContent->body());
                        //     fclose($fileHandle);
                        //     $this->line("File saved: " . $fileUrl);
                        // } else {
                        //     $this->line("Failed to download file with ID: " . $fileId);
                        // }
                    }
                }
            }
        }
        $this->line('success!');
    }

    protected function putYaFile($localFilePath, $filename)
    {
        $yaAccessKeyId = env('YA_ACCESS_KEY_KEY_ID');
        $yaAccessSecret = env('YA_ACCESS_KEY_SECRET');

        $s3Client = new S3Client([
            'version' => 'latest',
            'region'  => 'ru-central1',
            'endpoint' => 'https://storage.yandexcloud.net',
            'credentials' => [
                'key'    => $yaAccessKeyId,
                'secret' => $yaAccessSecret,
            ],
        ]);
        $fileUri = '';
        try {
            $result = $s3Client->putObject([
                'Bucket' => 'april-test',
                'Key'    => 'test/' . $filename,
                'SourceFile' => $localFilePath,
            ]);
            $this->line("Файл успешно загружен: " . $result['ObjectURL'] . "\n");
            $fileUri = $result['ObjectURL'];
            return $fileUri;
        } catch (AwsException $e) {
            $this->line("Ошибка загрузки файла: " . $e->getMessage() . "\n");
            return $fileUri;
        }
    }
    protected function getYascribation($fileUri)
    {
        $iamToken = env('I_AM');
        $iamFolder = env('YA_FOLDER_ID');
        $yaAccessKeyId = env('YA_ACCESS_KEY_KEY_ID');
        $yaAccessSecret = env('YA_ACCESS_KEY_SECRET');

        // $yaYrl = 'https://stt.api.cloud.yandex.net/speech/v1/stt:recognize'; //sync
        $yaYrl = 'https://transcribe.api.cloud.yandex.net/speech/stt/v2/longRunningRecognize'; //async
        $audioUri = 'https://cloudpbx.beeline.ru/api/downloadCallRecording/6ecf0c99f0c7b0795b57b05170b5066/record/f017f526fd75682f052907a0d8a48d1b'; // Путь к вашему аудиофайлу в Yandex Object Storage
        $localFilePath = storage_path('app/public/audio/file.mp3');
        // $fileUri = 'https://april-test.storage.yandexcloud.net/test/audio.mp3';


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
                // $this->line($responseData);


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
                // echo ($operationStatusResponse);
                $done = $operationStatusResponse['done'];


                if ($operationStatusResponse->successful()) {
                    $operationStatus = $operationStatusResponse->json();
                    if (!$operationStatus['done']) {
                        sleep(1);
                        // Логирование, что операция все еще в процессе
                        // $this->line("Попытка {$attempt}: операция {$operationId} все еще выполняется.");
                    } else {
                        if (isset($operationStatus['response']['results'])) {
                            // Операция завершена успешно, обработка результатов
                            $this->line("Операция {$operationId} завершена.");
                        } elseif (isset($operationStatus['error'])) {
                            // Ошибка в процессе выполнения операции
                            // $this->line("Ошибка операции: " . $operationStatus['error']['message']);
                        }
                    }
                } else {
                    // Логирование ошибки запроса к API
                    // $this->line("Ошибка опроса статуса операции: " . $operationStatusResponse->body());
                }
                // sleep(10);
                $attempt++;
            }
            $text = '';

            if ($done) {
                if (!empty($operationStatusResponse['response'])) {
                    if (!empty($operationStatusResponse['response']['chunks'])) {
                        $chunks = $operationStatusResponse['response']['chunks'];

                        foreach ($chunks as $chunk) {
                            foreach ($chunk['alternatives'] as $alternative) {
                                // Выводим текст каждого фрагмента разговора
                                // echo "Текст: " . $alternative['text'] . "\n";
                                // echo "Уверенность: " . $alternative['confidence'] . "\n";
                                $text .= $alternative['text'] . "\n";
                                // Проходим по каждому слову для анализа временных меток
                                // foreach ($alternative['words'] as $word) {
                                //     echo "Слово: " . $word['word'] . ", время начала: " . $word['startTime'] . ", время окончания: " . $word['endTime'] . "\n";
                                // }
                            }
                        }
                    } else {
                        $this->line(json_encode('no chunks'));

                        $this->line(json_encode($operationStatusResponse['response']));
                    }
                } else {
                    $this->line(json_encode('no response'));

                    $this->line(json_encode($operationStatusResponse));
                }
            }
            $this->line("TEXT: " . $text);
            // }
        } else {
            $this->error("Ошибка запроса! Статус: " . $response->status());
            $this->line("Ответ: " . $response->body());
        }
    }
}
