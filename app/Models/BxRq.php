<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BxRq extends Model
{
    use HasFactory;

    protected $fillable = [

        'portal_id',
        'name',
        'code',
        'type',
        'bitrix_id',
        'xml_id',
        'entity_type_id',
        'country_id',
        'is_active',
        'sort',
    ];

    public function portal()
    {
        return $this->belongsTo(Portal::class);
    }

    public function bitrixfields()
    {
        return $this->morphMany(Bitrixfield::class, 'bx_rq');
    }
}
