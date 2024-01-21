<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Counter extends Model
{
    use HasFactory;

    public function templates()
    {
        return $this->belongsToMany(Template::class, 'template_counter')
            ->withPivot('value', 'prefix', 'day', 'year', 'month', 'count', 'size');
    }
}
