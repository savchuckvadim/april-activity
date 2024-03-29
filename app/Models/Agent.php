<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    use HasFactory;
    protected $fillable = ['number', 'type', 'portalId', 'name', 'code'];
   
    public function portal()
    {
        return $this->belongsTo(Portal::class, 'portalId', 'id');
    }

    
    public function rq()
    {
        return $this->hasOne(Rq::class, 'agentId', 'number');
    }
}
