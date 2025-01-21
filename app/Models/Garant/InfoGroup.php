<?php

namespace App\Models\Garant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfoGroup extends Model
{
    use HasFactory;

    public function infoblocks()
    {
        return $this->belongsToMany(Infoblock::class, 'infoblock_info_group');
    }
    
}
