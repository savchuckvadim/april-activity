<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'code',
        'fieldNumber',
        'fieldId',
        'order',
        'value',
        'bitixId',

    ];
}
