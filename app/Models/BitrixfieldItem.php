<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BitrixfieldItem extends Model
{
    use HasFactory;
    protected $fillable = [
        
    ];

    public function bitrixField()
    {
        return $this->belongsTo(Bitrixfield::class);
    }
}
