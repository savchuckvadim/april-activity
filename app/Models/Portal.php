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


        $cryptdomain = Crypt::encryptString($domain);


        if (Portal::where('domain', $cryptdomain)->exists()) {
            throw new \InvalidArgumentException("Portal with this domain already exists.");
        }


        if (Portal::where('domain', $cryptdomain)->exists()) {
            throw new \InvalidArgumentException("Portal with this domain already exists.");
        }



        $cryptclientId = Crypt::encryptString($clientId);
        $cryptsecret  = Crypt::encryptString($secret);
        $crypthook =   Crypt::encryptString($hook);

        $portal = new Portal([
            'domain' => $cryptdomain,
            'C_REST_CLIENT_ID' => $cryptclientId,
            'C_REST_CLIENT_SECRET' => $cryptsecret,
            'C_REST_WEB_HOOK_URL' => $crypthook,
        ]);

        $portal->save();

        $resultPortal = $portal->getPortal($portal->id);


        if (!$resultPortal) {
            throw new \Exception("Failed to retrieve the saved portal.");
        }


        return $resultPortal;
    }



    public static function getPortal($portalId)
    {

        $portal = Portal::find($portalId);

        if (!$portal) {
            return response([
                'message' => 'portal does not exist!'
            ]);
        }

        return [
            'id' => $portal->id,
            'domain' => Crypt::decryptString($portal->domain),
            'C_REST_CLIENT_ID' => Crypt::decryptString($portal->C_REST_CLIENT_ID),
            'C_REST_CLIENT_SECRET' => Crypt::decryptString($portal->C_REST_CLIENT_SECRET),
            'C_REST_WEB_HOOK_URL' => Crypt::decryptString($portal->C_REST_WEB_HOOK_URL),
        ];
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
