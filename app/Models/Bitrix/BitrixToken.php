<?php

namespace App\Models\Bitrix;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;


class BitrixToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'bitrix_app_id',
    
        'client_id',
        'client_secret',
        'access_token',
        'refresh_token',
        'expires_at',
        'application_token',
        'member_id'
    ];

    public function bitrixApp()
    {
        return $this->belongsTo(BitrixApp::class);
    }




    public function getClientId()
    {
        return Crypt::decryptString($this->client_id);
    }

    public function getSecret()
    {
        return Crypt::decryptString($this->client_secret);
    }


    public function getAccessToken()
    {
        return Crypt::decryptString($this->access_token);
    }
    public function getRefreshToken()
    {
        return Crypt::decryptString($this->refresh_token);
    }
    public function getApplicationToken()
    {
        return Crypt::decryptString($this->application_token);
    }
    public function getMemberId()
    {
        return Crypt::decryptString($this->member_id);
    }

    
}
