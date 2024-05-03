<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deal extends Model
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
        'regions'
        
       
        
        
       
        // 'product',
      
        
    ];


    public function portal()
    {
        return $this->belongsTo(Portal::class);
    }
}
