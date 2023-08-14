<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfferMaster extends Model
{
    use HasFactory;

    public function master(){
        return $this->hasMany(User::class);
    }
    public function offer(){
        return $this->hasMany(Offer::class);
    }

}
