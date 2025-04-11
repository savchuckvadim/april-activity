<?php

namespace App\Models\Bitrix;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BitrixAppSecret extends Model
{
    use HasFactory;

    protected $fillable = [

        'group',
        'type',
        'code',
    
        'client_id',
        'client_secret'
    ];

    public function getClientId()
    {
        return Crypt::decryptString($this->client_id);
    }

    public function getSecret()
    {
        return Crypt::decryptString($this->client_secret);
    }
}
