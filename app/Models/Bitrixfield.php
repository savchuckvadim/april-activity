<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bitrixfield extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'name',
        'code',
        'type',
        'bitrixId',
        'bitrixCamelId',
        'parent',
        'parent_type', //название файла в родительской модели напр list или deal - к чему относится field

    ];

    public function entity()
    {
        return $this->morphTo();
    }
}
