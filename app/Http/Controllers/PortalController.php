<?php

namespace App\Http\Controllers;

use App\Models\Portal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class PortalController extends Controller
{
    public static function setPortal($number, $domain, $key, $clientId, $secret, $hook)
    {
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
        }

        $cryptkey = Crypt::encryptString($key);
        $cryptclientId = Crypt::encryptString($clientId);
        $cryptsecret  = Crypt::encryptString($secret);
        $crypthook =   Crypt::encryptString($hook);

        if (Portal::where('number', $number)->exists()) {
            $portal = Portal::where('number', $number)->first();
        } else {
            $portal = new Portal([
                'number' => $number,
                'domain' => $domain,
                'key' => $cryptkey,
                'C_REST_CLIENT_ID' => $cryptclientId,
                'C_REST_CLIENT_SECRET' => $cryptsecret,
                'C_REST_WEB_HOOK_URL' => $crypthook,
            ]);
        }





        $portal->save();

        $resultPortal = $portal = Portal::where('domain', $domain)->first();


        if (!$resultPortal) {
            return response([
                'resultCode' => 1,
                'message' => 'invalid resultPortal not found Portal with the domain: ' . $domain
            ]);
        }


        return response(['portal' => $portal]);
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
            'id' => $portal->id,
            'domain' => $domain,
            'key' => $portal->getKey(),
            'C_REST_CLIENT_ID' => $portal->getClientId(),
            'C_REST_CLIENT_SECRET' => $portal->getSecret(),
            'C_REST_WEB_HOOK_URL' => $portal->getHook(),
        ]);
    }
    public static function getPortalById($portalId)
    {

        $portal = Portal::find($portalId);

        if (!$portal) {
            return response([
                'resultCode' => 1,
                
                'message' => 'portal does not exist!'
            ]);
        }

        return response([
            'id' => $portal->id,
            'portal' => $portal,
            'key' => $portal->getKey(),
            'C_REST_CLIENT_ID' => $portal->getClientId(),
            'C_REST_CLIENT_SECRET' => $portal->getSecret(),
            'C_REST_WEB_HOOK_URL' => $portal->getHook(),
        ]);
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

    // public function getDomain()
    // {
    //     return Crypt::decryptString($this->domain);
    // }

   
}
