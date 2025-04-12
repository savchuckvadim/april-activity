<?php

namespace App\Services\BitrixApp;

use App\Models\Portal;
use Exception;
use App\Http\Resources\BitrixApp\BitrixAppResource;

class BitrixAppService
{
    public static function getAppWithToken(string $code, string $domain)
    {
        $portal = Portal::where('domain', $domain)
            ->firstOrFail();

        $app = $portal->bitrixApps()
            ->where('code', $code)
            ->with(['token', 'portal', 'placements'])
            ->firstOrFail();

        if (empty($app)) {
            throw new Exception('Bitrix App не найден');
        }

        if (empty($app->token)) {
            throw new Exception('Токен не найден');
        }

        return [
            'app' =>  new BitrixAppResource($app),
            'token' => $app->token
        ];
    }
}
