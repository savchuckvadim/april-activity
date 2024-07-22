<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;
    protected $with = ['measure', 'portalContracts.portal'];

    public function portals()
    {
        return $this->belongsToMany(Portal::class, 'portal_contracts')
                    ->withPivot(['id', 'portal_measure_id'])
                    ->using(PortalContract::class);
    }
}
