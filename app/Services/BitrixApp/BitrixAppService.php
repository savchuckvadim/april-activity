<?php
namespace App\Services\BitrixApp;

use App\Models\Bitrix\BitrixApp;
use Exception;

class BitrixAppService
{
    public static function getAppWithToken(string $code, string $domain)
    {
        $app = BitrixApp::with('token')
            ->where('code', $code)
            ->where('domain', $domain)
            ->first();

        if (empty($app)) {
            throw new Exception('Bitrix App не найден');
        }

        if (empty($app->token)) {
            throw new Exception('Токен не найден');
        }

        return [
          'app' =>  $app,
          'token' => $app->token
        ];
    }
}
