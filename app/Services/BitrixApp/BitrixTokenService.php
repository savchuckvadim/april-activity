<?php
namespace App\Services\BitrixApp;

use App\Models\Bitrix\BitrixToken;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;

class BitrixTokenService
{
    /**
     * Проверяет и при необходимости обновляет токен.
     */
    public static function refreshIfExpired(BitrixToken $token): void
    {
        // if (!$token->expires_at || Carbon::parse($token->expires_at)->isFuture()) {
        //     return; // Токен ещё валиден
        // }

        $clientId = $token->getClientId();
        $clientSecret = $token->getSecret();
        $refreshToken = $token->getRefreshToken();

        $response = Http::get('https://oauth.bitrix.info/oauth/token/', [
            'grant_type' => 'refresh_token',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'refresh_token' => $refreshToken,
        ]);

        if (!$response->ok()) {
            throw new Exception('Ошибка обновления токена: ' . $response->body());
        }

        $data = $response->json();

        $token->update([
            'access_token' => Crypt::encryptString($data['access_token']),
            'refresh_token' => Crypt::encryptString($data['refresh_token']),
            'expires_at' => now()->addSeconds($data['expires_in']),
        ]);
    }
}
