<?php

namespace App\Models\Konstructor;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderCurrent extends Model
{
    use HasFactory;

    protected $fillable = [
        'domain',
        'portalId',
        'bxUserId',
        'agentId',
        'providerName',
        'name'

    ];
}
