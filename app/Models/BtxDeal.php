<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BtxDeal extends Model
{
    use HasFactory;

    public function categories()
    {
        return $this->morphMany(BtxCategory::class, 'entity');
    }
}
