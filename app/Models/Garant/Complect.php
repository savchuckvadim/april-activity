<?php

namespace App\Models\Garant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Complect extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        // Добавьте любые другие столбцы, которые есть в таблице `complects`
    ];

    /**
     * Связь многие ко многим с инфоблоками
     */
    public function infoblocks()
    {
        return $this->belongsToMany(Infoblock::class, 'complect_infoblock');
    }
}
