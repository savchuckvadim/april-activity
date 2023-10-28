<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Field extends Model
{
    use HasFactory;


    protected $fillable = [
        'number',
        'name',
        'code',
        'type',
        'isGeneral',
        'isDefault',
        'isRequired',
        'value',
        'description',
        'bitixId',
        'bitrixTemplateId',
        'isActive',
        'isPlural',
    ];

    public function items()
    {
        return $this->hasMany(FItem::class, 'fieldId', 'id');
    }

}
