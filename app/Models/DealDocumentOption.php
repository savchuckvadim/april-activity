<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DealDocumentOption extends Model
{
    use HasFactory;
    protected $fillable = [
        'dealId',
        'dealDocumentFavoriteId',
        'salePhrase',
        'withStamp',
        'isPriceFirst',
        'withManager',
        'iblocksStyle',
        'describStyle',
        'otherStyle',
        'priceDiscount',
        'priceYear',
        'priceDefault',
        'priceSupply'    ,
        'priceOptions',
        'otherPrice',
        'otherSettings' 
      
    ];
}
