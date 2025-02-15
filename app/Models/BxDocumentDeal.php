<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BxDocumentDeal extends Model
{
    use HasFactory;
    protected $fillable = [
        'app',
        'contract',
        'currentComplect',
        'dealId',
        'dealName',
        'domain',
        'global',
        'od',
        'portalId',
        'result',
        'rows',
        'userId',
        'regions',
        'title',
        'isFavorite',
        'clientType'
        // 'product',


    ];

    public function portal()
    {
        return $this->belongsTo(Portal::class);
    }

    public function favorites()
    {
        return $this->hasMany(DealDocumentFavorite::class, 'dealId');
    }
}
