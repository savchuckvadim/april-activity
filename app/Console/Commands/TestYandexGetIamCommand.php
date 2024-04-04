<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\Algorithm\PS256;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Jose\Component\Core\AlgorithmManagerFactory;




class TestYandexGetIamCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ya:iam';

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


        $keyJsonPath = base_path('keys/key.json');
        $keyJson = file_get_contents($keyJsonPath);
        $keyData = json_decode($keyJson, true);

        // Подготовка параметров для JWK
        $privateKeyPEM = $keyData['private_key'];
        $keyId = $keyData['id'];
        $algorithm = 'PS256'; // В вашем случае используется RSA, PS256 подходит для RSA PSS
        $startPrivateKey = strpos($privateKeyPEM, "-----BEGIN PRIVATE KEY-----");
        $endPrivateKey = strpos($privateKeyPEM, "-----END PRIVATE KEY-----") + strlen("-----END PRIVATE KEY-----");
        $cleanPrivateKey = substr($privateKeyPEM, $startPrivateKey, $endPrivateKey - $startPrivateKey);
        // Создание объекта JWK из приватного ключа
        $key = JWKFactory::createFromKey(
            $cleanPrivateKey,
            null, // Пароль, если ключ защищен паролем (в вашем случае он не используется)
            [
                'alg' => $algorithm,
                'kid' => $keyId,
                'use' => 'sig',
                'kty' => 'RSA'
            ]
        );

        // Создание AlgorithmManager и JWSBuilder
        $algorithmManagerFactory = new AlgorithmManagerFactory();
        $algorithmManagerFactory->add($algorithm, new PS256());
        $signatureAlgorithmManager = $algorithmManagerFactory->create([$algorithm]);

        // Инициализация JWSBuilder с менеджером алгоритмов
        $jwsBuilder = new JWSBuilder($signatureAlgorithmManager);

        // Текущее время и время истечения токена
        $now = time();
        $expiration = $now + 3600; // Время истечения токена, например, 1 час

        // Формирование полезной нагрузки (payload) JWT
        $payload = json_encode([
            'iss' => $keyData['service_account_id'],
            'aud' => 'https://iam.api.cloud.yandex.net/iam/v1/tokens',
            'iat' => $now,
            'exp' => $expiration,
        ]);

        // Создание и подписание JWS
        $jws = $jwsBuilder->create()
            ->withPayload($payload)
            ->addSignature($key, ['alg' => $algorithm, 'kid' => $keyId])
            ->build();

        // Сериализация JWS в компактный формат
        $serializer = new CompactSerializer();
        $jwt = $serializer->serialize($jws, 0);
        $this->line("Ответ: " . print_r($jwt, true));

        $client = new Client();

        try {
            $response = $client->request('POST', 'https://iam.api.cloud.yandex.net/iam/v1/tokens', [
                'json' => ['jwt' => $jwt]
            ]);
        
            $responseData = json_decode($response->getBody()->getContents(), true);
            echo "IAM Token: " . $responseData['iamToken'];
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            echo "Ошибка: " . $e->getMessage();
        }
        //update ya token
        $client = new \GuzzleHttp\Client();
        $iamToken = env('I_AM');
        $iamFolder = env('YA_FOLDER_ID');
        // $response = $client->post('https://iam.api.cloud.yandex.net/iam/v1/tokens', [
        //     'json' => [
        //         'yandexPassportOauthToken' => $iamToken
        //     ]
        // ]);

        // $body = $response->getBody();
        // $data = json_decode($body);
        // $iamToken = $data->iamToken; // Новый IAM токен
        // $this->line("Ответ: " . $iamToken);


        // $yaYrl = 'https://stt.api.cloud.yandex.net/speech/v1/stt:recognize'; //sync
        // $yaYrl = 'https://transcribe.api.cloud.yandex.net/speech/stt/v2/longRunningRecognize'; //async


        // $audioUri = 'https://cloudpbx.beeline.ru/api/downloadCallRecording/6ecf0c99f0c7b0795b57b05170b5066/record/f017f526fd75682f052907a0d8a48d1b'; // Путь к вашему аудиофайлу в Yandex Object Storage

        // $response = Http::withHeaders([
        //     'Authorization' => "Bearer {$iamToken}",
        //     'Content-Type' => 'application/json',
        // ])->post($yaYrl, [
        //     'config' => [
        //         'folderId' => $iamFolder, // Добавление folderId здесь

        //         'specification' => [
        //             'languageCode' => 'ru-RU',
        //             'model' => 'general',
        //             'profanityFilter' => false,
        //             'audioEncoding' => 'MP3',
        //             'sampleRateHertz' => 48000,
        //             'audioChannelCount' => 1,
        //             'rawResults' => false,
        //         ]
        //     ],
        //     'audio' => [
        //         'uri' => $audioUri,
        //     ]
        // ]);

        // if ($response->successful()) {
        //     $responseData = $response->json();
        //     // Обработка успешного ответа
        // } else {
        //     // Обработка ошибки
        //     $error = $response->body();
        //     // Действия в случае ошибки
        // }
        // if ($response->successful()) {
        // } else {
        //     $this->error("Ошибка запроса! Статус: " . $response->status());
        //     $this->line("Ответ: " . $response->body());
        // }
    }
}
