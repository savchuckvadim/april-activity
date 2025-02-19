<?php

namespace App\Console\Commands;

use App\Http\Controllers\BeelineController;
use App\Http\Controllers\BitrixController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

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
        $method = '/crm.activity.get';
        // $hook = env('TEST_HOOK');
        $hook = BitrixController::getHook($domain);
        $dealId = 12202;
        $fields =
            [
                'OWNER_TYPE_ID' => 2, // 2- deal 3 - company
                'OWNER_ID' => 12198, // 2976,
                "TYPE_ID" => 2 // Тип активности - Звонок
            ];

        $url = $hook . $method;
        $data = [
            // "ownerTypeId" => 2,
            // "ownerId" => $dealId,
            'id' => 645286,
            'filter' => $fields,
            "order" => [
                "START_TIME" => "ASC" // Например, сортировать по времени начала звонка
            ]


        ];

        $response = Http::get($url, $data);
        $responseData = $response->json();
        if (!empty($responseData['result'])) {
            // $this->line(json_encode($responseData));
            // foreach ($responseData['result'] as $activity) {
            // $this->line(json_encode($activity));
            if (isset($responseData['result']['FILES'])) {

                foreach ($responseData['result']['FILES'] as  $file) {
                    $fileId = $file['id'];
                    $fileUrl = $file['url'];
                    $this->line(json_encode('file'));
                    // $this->line(json_encode($file));
                    $this->line(json_encode($fileId));
                    $this->line(json_encode('url'));
                    $this->line(json_encode($fileUrl));

                    // Здесь ваш код для обработки URL файла

                    $hook = BitrixController::getHook($domain); // Это твой вебхук
                    $fileUrlWithHook = $fileUrl . '&' . parse_url($hook, PHP_URL_QUERY);
                    $response = Http::withOptions(['allow_redirects' => true])->get($fileUrlWithHook);

                    $this->line("Status Code: " . $response->status());
                    $this->line("Headers: " . json_encode($response->headers()));
                    $this->line("Body: " . substr($response->body(), 0, 500));


                    $fileContent = Http::get($fileUrl);
                    if ($fileContent->successful()) {
                        //     // Замените 'path/to/your/directory/' на путь к каталогу, где вы хотите сохранить файлы
                        $fileName = $fileId;
                        $path = storage_path('app' . DIRECTORY_SEPARATOR . 'audio' . DIRECTORY_SEPARATOR . $domain . DIRECTORY_SEPARATOR . $fileName);
                        $directoryPath = dirname($path);
                        if (!file_exists($directoryPath)) {
                            mkdir($directoryPath, 0777, true); // Рекурсивное создание директорий
                        }
                        //     // Открытие файла для записи в бинарном режиме
                        $fileHandle = fopen($path, 'wb');
                        if ($fileHandle === false) {
                            // Обработка ошибки открытия файла
                            $this->line("Unable to open file for writing: " . $path);
                            return;
                        }

                        // Запись содержимого в файл
                        fwrite($fileHandle, $fileContent->body());
                        fclose($fileHandle);
                        $this->line("File saved: " . $fileUrl);
                    } else {
                        $this->line("Failed to download file with ID: " . $fileId);
                    }
                }
            }
            // }
        }
    }
}
