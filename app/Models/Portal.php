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
        'C_REST_CLIENT_ID',
        'C_REST_CLIENT_SECRET',
        'C_REST_WEB_HOOK_URL',
    ];

    public static function setPortal($domain, $clientId, $secret, $hook)
    {
        if (empty($domain) || empty($clientId) || empty($secret) || empty($hook)) {
            throw new \InvalidArgumentException("All parameters are required.");
        }


        if (Portal::where('domain', $domain)->exists()) {
            return response([ 'message' => "Portal with this domain already exists."]);
        }

        $cryptclientId = Crypt::encryptString($clientId);
        $cryptsecret  = Crypt::encryptString($secret);
        $crypthook =   Crypt::encryptString($hook);

        $portal = new Portal([
            'domain' => $domain,
            'C_REST_CLIENT_ID' => $cryptclientId,
            'C_REST_CLIENT_SECRET' => $cryptsecret,
            'C_REST_WEB_HOOK_URL' => $crypthook,
        ]);

        $portal->save();

        $resultPortal = $portal->getPortal($domain);


        if (!$resultPortal) {
            throw new \Exception("Failed to retrieve the saved portal.");
        }


        return response([ 'portal' => $portal]);
    }



    public static function getPortal($domain)
    {

        $portal = Portal::where('domain', $domain)->first();
     
        if (!$portal) {
            return response([
                'message' => 'portal does not exist!'
            ]);
        }

        return response([
            'id' => $portal->id,
            'domain' => $portal->getDomain(),
            'C_REST_CLIENT_ID' => $portal->getClientId(),
            'C_REST_CLIENT_SECRET' => $portal->getSecret(),
            'C_REST_WEB_HOOK_URL' => $portal->getHook(),
        ]);

    }

    public function getDomain()
    {
        return Crypt::decryptString($this->domain);
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
