<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Smart extends Model
{
    use HasFactory;


    protected $fillable = [
        'id',
        'type',
        'group',
        'name',
        'title',
        'entityTypeId',
        'portal_id'

    ];

    public function portal()
    {
        return $this->belongsTo(Portal::class);
    }
}
