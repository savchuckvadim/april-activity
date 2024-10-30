<?php

namespace App\Http\Controllers\Outer;

use App\Http\Controllers\APIController;
use App\Http\Controllers\Controller;
use App\Http\Resources\PortalFrontResource;
use App\Http\Resources\PortalOuterResource;
use App\Models\Portal;


class PortalController extends Controller
{



    public static function get($domain)
    {
        // $cacheKey = 'portal_' . $domain;
        // $cachedPortalData = Cache::get($cacheKey);

        // if (!is_null($cachedPortalData)) {
        //     Log::channel('telegram')->info('APRIL_ONLINE', [
        //         'log from cache getPortal'   =>
        //         $cachedPortalData

        //     ]);
        //     return APIController::getSuccess([
        //         'data' => [
        //             'portal' => $cachedPortalData
        //         ]
        //     ]); // Возвращаем данные в формате response
        // }

        $portal = Portal::where('domain', $domain)->first();
        if (!$portal) {
            return response([
                'resultCode' => 1,
                'message' => 'portal does not exist!'
            ], 404);
        }

        $portalData = new PortalFrontResource($portal);
        $portal_data = new PortalOuterResource($portal, $domain);



        // Cache::put($cacheKey, $portalData, now()->addMinutes(10)); // Кешируем данные портала
        return APIController::getSuccess(['portal_old' => $portalData, 'portal' => $portal_data]); // Возвращаем данные в формате response
    }
}
