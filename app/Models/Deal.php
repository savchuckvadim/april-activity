<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deal extends Model
{
    use HasFactory;

    protected $fillable = [
        'dealId',
        'userId',
        'domain',
        'dealName',
        'app',
        'global',
        'currentComplect',
        'od',
        'result',
        'contract',
        'product',
        'rows'
        
    ];
}
