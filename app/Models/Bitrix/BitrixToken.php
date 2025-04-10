<?php

namespace App\Models\Bitrix;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'application_token'
    ];

    public function bitrixApp()
    {
        return $this->belongsTo(BitrixApp::class);
    }
}
