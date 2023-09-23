<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;



class Portal extends Model
{
    use HasFactory;

    protected $fillable = [
        'domain',
        'key',
        'C_REST_CLIENT_ID',
        'C_REST_CLIENT_SECRET',
        'C_REST_WEB_HOOK_URL',
    ];

    public static function setPortal($domain, $key, $clientId, $secret, $hook)
    {
        if (empty($domain) || empty($key) || empty($clientId) || empty($secret) || empty($hook)) {
            return response(['resultCode' => 1, 'message' => "invalid data", 'data' => [
                'domain' => $domain,
                'key' => $key,
                'C_REST_CLIENT_ID' => $clientId,
                'C_REST_CLIENT_SECRET' => $secret,
                'C_REST_WEB_HOOK_URL' => $hook,
            ]]);
        }


        if (Portal::where('domain', $domain)->exists()) {
            return response(['resultCode' => 1, 'message' => "Portal with this domain already exists."]);
        }

        $cryptkey = Crypt::encryptString($key);
        $cryptclientId = Crypt::encryptString($clientId);
        $cryptsecret  = Crypt::encryptString($secret);
        $crypthook =   Crypt::encryptString($hook);

        $portal = new Portal([
            'domain' => $domain,
            'key' => $cryptkey,
            'C_REST_CLIENT_ID' => $cryptclientId,
            'C_REST_CLIENT_SECRET' => $cryptsecret,
            'C_REST_WEB_HOOK_URL' => $crypthook,
        ]);

        $portal->save();

        $resultPortal = $portal->getPortal($domain);


        if (!$resultPortal) {
            return response([
                'resultCode' => 1,
                'message' => 'invalid resultPortal not found Portal with the domain: '.$domain
            ]);
        }


        return response([ 'portal' => $portal]);
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

    // public function getDomain()
    // {
    //     return Crypt::decryptString($this->domain);
    // }

    public function getKey()
    {
        return Crypt::decryptString($this->key);
    }

    public function getClientId()
    {
        return Crypt::decryptString($this->C_REST_CLIENT_ID);
    }

    public function getSecret()
    {
        return Crypt::decryptString($this->C_REST_CLIENT_SECRET);
    }
    public function getHook()
    {
        return Crypt::decryptString($this->C_REST_WEB_HOOK_URL);
    }
}
