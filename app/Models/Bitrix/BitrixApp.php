<?php

namespace App\Models\Bitrix;

use App\Models\Portal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BitrixApp extends Model
{
    use HasFactory;

    protected $fillable = [
        'portal_id',
        'group',
        'type',
        'code',
        'status'
    ];

    public function portal()
    {
        return $this->belongsTo(Portal::class);
    }

    public function token()
    {
        return $this->hasOne(BitrixToken::class);
    }
    public function placements()
    {
        return $this->hasMany(BitrixAppPlacement::class);
    }

    public function settings()
    {
        return $this->morphMany(BitrixSetting::class, 'settingable');
    }
}
