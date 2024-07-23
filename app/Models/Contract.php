<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;
    // protected $with = ['measure', 'portalContracts.portal'];

    protected $fillable = ['name', 'number', 'title', 'code', 'type', 'template', 'order', 'coefficient', 'prepayment', 'discount', 'productName', 'withPrepayment'];

    // Отношение к PortalContract через связующую таблицу
    public function portalContracts()
    {
        return $this->hasMany(PortalContract::class);
    }
}
