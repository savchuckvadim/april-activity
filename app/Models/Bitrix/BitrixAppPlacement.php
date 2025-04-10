<?php

namespace App\Models\Bitrix;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BitrixAppPlacement extends Model
{
    use HasFactory;
    protected $fillable = [
        'bitrix_app_id',
        'code',
        'type',
        'group',
        'status',
        'bitrix_heandler',
        'public_heandler',
        'bitrix_codes',
    ];

    public function bitrixApp()
    {
        return $this->belongsTo(BitrixApp::class);
    }

    public function settings()
    {
        return $this->morphMany(BitrixSetting::class, 'settingable');
    }
}
