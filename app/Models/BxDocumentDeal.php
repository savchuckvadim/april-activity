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
        'serviceSmartId',
        'smartId',
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

    public function options()
    {
        return $this->hasOne(DealDocumentOption::class, 'dealDocumentFavoriteId', 'id');
    }
}
