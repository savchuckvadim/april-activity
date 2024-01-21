<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Timezone extends Model
{
    use HasFactory;


    protected $fillable = [
        'id', 'type',  'name', 'title', 'value', 'portal_id'
    ];

    public function portal()
    {
        return $this->belongsTo(Portal::class);
    }
}
