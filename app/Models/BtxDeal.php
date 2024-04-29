<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BtxDeal extends Model
{
    use HasFactory;


    public function portal()
    {
        return $this->belongsTo(Portal::class);
    }


    
    public function categories()
    {
        return $this->morphMany(BtxCategory::class, 'entity');
    }

    public function fields()
    {
        return $this->morphMany(Bitrixfield::class, 'entity');
    }

    public function callingFields()
    {
        return $this->morphMany(Bitrixfield::class, 'entity')->where('parent_type', 'calling');
    }

    public function offerFields()
    {
        return $this->morphMany(Bitrixfield::class, 'entity')->where('parent_type', 'konstructor');
    }
}
