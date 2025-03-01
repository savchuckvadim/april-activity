<?php

namespace App\Services\Yandex;

use App\Http\Controllers\ALogController;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\Algorithm\PS256;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Jose\Component\Core\AlgorithmManagerFactory;
use Illuminate\Support\Facades\Cache;


class AuthService
{
    protected string $tokensUrl;
    protected string $keyJsonPath;
    protected array $keyData;
    protected Client $client;

    public function __construct()
    {
        // URL API для получения IAM-токена
        $this->tokensUrl = 'https://iam.api.cloud.yandex.net/iam/v1/tokens';

        // Путь к JSON-файлу с ключами
        $this->keyJsonPath = base_path('keys/key.json');

        // Загружаем JSON-файл с ключами
        if (!file_exists($this->keyJsonPath)) {
            throw new \Exception("Key file not found: {$this->keyJsonPath}");
        }
        $this->keyData = json_decode(file_get_contents($this->keyJsonPath), true);

        // Инициализируем Guzzle клиент
        $this->client = new Client();
    }

    public function getIamToken()
    {
        // Проверяем кеш
        if (Cache::has('iam_token')) {
            return Cache::get('iam_token');
        }

        // Генерируем JWT
        $jwt = $this->generateJwt();

        try {
            $response = $this->client->request('POST', $this->tokensUrl, [
                'json' => ['jwt' => $jwt]
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);
            $iamToken = $responseData['iamToken'];

            // Сохраняем токен в кэш на 11 часов 55 минут (чтобы обновлять заранее)
            Cache::put('iam_token', $iamToken, now()->addHours(11)->addMinutes(55));

            return $iamToken;
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            ALogController::push('IAM Error', ['error' => $e->getMessage(), 'from' => 'get IAM token']);
            return null;
        }
    }

    private function generateJwt(): string
    {
        $algorithm = 'PS256';
        $privateKeyPEM = $this->keyData['private_key'];
        $keyId = $this->keyData['id'];

        // Генерируем ключ
        $key = JWKFactory::createFromKey($privateKeyPEM, null, [
            'alg' => $algorithm,
            'kid' => $keyId,
            'use' => 'sig',
            'kty' => 'RSA'
        ]);

        // Создаем алгоритм подписания
        $algorithmManagerFactory = new AlgorithmManagerFactory();
        $algorithmManagerFactory->add($algorithm, new PS256());
        $signatureAlgorithmManager = $algorithmManagerFactory->create([$algorithm]);

        // Подготовка JWS
        $jwsBuilder = new JWSBuilder($signatureAlgorithmManager);
        $payload = json_encode([
            'iss' => $this->keyData['service_account_id'],
            'aud' => $this->tokensUrl,
            'iat' => time(),
            'exp' => time() + 3600, // 1 час
        ]);

        // Подписываем JWS
        $jws = $jwsBuilder->create()
            ->withPayload($payload)
            ->addSignature($key, ['alg' => $algorithm, 'kid' => $keyId])
            ->build();

        return (new CompactSerializer())->serialize($jws, 0);
    }
}
