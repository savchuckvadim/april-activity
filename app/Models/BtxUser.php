<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BtxUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 
        'portal_id'
    ];
    public function portal()
    {
        return $this->belongsTo(Portal::class);
    }
    public function bitrixfields()
    {
        return $this->morphMany(Bitrixfield::class, 'entity');
    }

}
