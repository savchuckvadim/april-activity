<?php

namespace App\Services\BitrixApp;

use App\Models\Portal;
use Exception;
use App\Http\Resources\BitrixApp\BitrixAppResource;
use App\Services\BitrixApp\BitrixTokenService;
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
        if ($app->token) {
            BitrixTokenService::refreshIfExpired($app->token);
        }
        $result = new BitrixAppResource($app);
        return $result;
    }

    public static function getPortalAppsWithToken(string $domain)
    {
        $portal = Portal::where('domain', $domain)
            ->firstOrFail();

        if (empty($portal)) {
            throw new Exception('portal не найден');
        }
        $apps = $portal->bitrixApps()->with(['token', 'portal', 'placements'])->get();



        if (empty($apps)) {
            throw new Exception('apps не найден');
        }
        $result = BitrixAppResource::collection($apps);
        return $result;
    }
}
