<?php

namespace App\Http\Controllers;

use App\Models\Portal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class PortalController extends Controller
{
    public static function setPortal($number, $domain, $key, $clientId, $secret, $hook)
    {
        $data = [
            'number' => $number,
            'domain' => $domain,
            'key' => $key,
            'C_REST_CLIENT_ID' => $clientId,
            'C_REST_CLIENT_SECRET' => $secret,
            'C_REST_WEB_HOOK_URL' => $hook,
        ];
        if (empty($domain) || empty($key) || empty($clientId) || empty($secret) || empty($hook)) {
            return response([
                'resultCode' => 1, 'message' => "invalid data",
                'data' => [
                    'number' => $number,
                    'domain' => $domain,
                    'key' => $key,
                    'C_REST_CLIENT_ID' => $clientId,
                    'C_REST_CLIENT_SECRET' => $secret,
                    'C_REST_WEB_HOOK_URL' => $hook,
                ]
            ]);
        } else {
        }

        $data['key'] = Crypt::encryptString($key);
        $data['C_REST_CLIENT_ID'] = Crypt::encryptString($clientId);
        $data['C_REST_CLIENT_SECRET']  = Crypt::encryptString($secret);
        $data['C_REST_WEB_HOOK_URL'] =   Crypt::encryptString($hook);

        $portal = Portal::firstOrCreate(
            ['domain' => $domain], // Условия для поиска
            $data // Значения по умолчанию, если создается новая запись
        );
        $portal->save();
        return response([
            'resultCode' => 0,
            'message' => 'success',
            'portal' => [
                'id' => $portal->id,
                'number' => $portal->number,
                'domain' => $domain,
                // 'key' => $portal->getKey(),
                // 'C_REST_CLIENT_ID' => $portal->getClientId(),
                // 'C_REST_CLIENT_SECRET' => $portal->getSecret(),
                // 'C_REST_WEB_HOOK_URL' => $portal->getHook(),
            ]

        ]);





        $portal->save();



        return response([
            'resultCode' => 0,
            'message' => 'success',
            'portal' => [
                'id' => $portal->id,
                'number' => $portal->number,
                'domain' => $domain,
                'key' => $portal->key,
                'C_REST_CLIENT_ID' => $portal->C_REST_CLIENT_ID,
                'C_REST_CLIENT_SECRET' => $portal->C_REST_CLIENT_SECRET,
                'C_REST_WEB_HOOK_URL' => $portal->C_REST_WEB_HOOK_URL,
            ]

        ]);
    }
    public static function getPortal($domain)
    {

        $portal = Portal::where('domain', $domain)->first();

        if (!$portal) {
            return response([
                'resultCode' => 1,
                'message' => 'portal does not exist!'
            ]);
        }

        return response([
            'resultCode' => 0,
            'portal' => [
                'id' => $portal->id,
                'domain' => $domain,
                'key' => $portal->getKey(),
                'C_REST_CLIENT_ID' => $portal->getClientId(),
                'C_REST_CLIENT_SECRET' => $portal->getSecret(),
                'C_REST_WEB_HOOK_URL' => $portal->getHook(),
            ]

        ]);
    }
    public static function getPortalById($portalId)
    {

        $portal = Portal::find($portalId);
      
        if (!$portal) {
            return response([
                'resultCode' => 1,
                'portalId' => $portalId,
                'message' => 'portal not found'
            ]);
        }

        return response(

            [
                'resultCode' => 0,
                'message' => 'success',
                'portal' => [
                    'id' => $portal->id,
                    'number' => $portal->number,
                    'domain' => $portal->domain,
                    'key' => $portal->key,
                    'C_REST_CLIENT_ID' => $portal->C_REST_CLIENT_ID,
                    'C_REST_CLIENT_SECRET' => $portal->C_REST_CLIENT_SECRET,
                    'C_REST_WEB_HOOK_URL' => $portal->C_REST_WEB_HOOK_URL,
                    // 'key' => $portal->getKey(),
                    // 'C_REST_CLIENT_ID' => $portal->getClientId(),
                    // 'C_REST_CLIENT_SECRET' => $portal->getSecret(),
                    // 'C_REST_WEB_HOOK_URL' => $portal->getHook(),
                ]

            ]
        );
    }
    public static function getPortals()
    {

        $portals = Portal::all();

        if (!$portals) {
            return response([
                'resultCode' => 1,
                'message' => 'portals does not exist!'
            ]);
        }

        return response([
            'resultCode' => 0,
            'portals' => $portals
        ]);
    }

    public static function getInitial(){

        $initialPortal = Portal::getForm();
        $data = [
            'initial' => $initialPortal
        ];
        return APIController::getResponse(0, 'success', $data);

    }

  
    // public function getDomain()
    // {
    //     return Crypt::decryptString($this->domain);
    // }


}
