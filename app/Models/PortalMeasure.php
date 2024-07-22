<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortalMeasure extends Model
{
    use HasFactory;
    protected $table = 'portal_measure'; // Указание, какая таблица используется для этой модели

    
    public function measure() {
        return $this->belongsTo(Measure::class);
    }

    public function portal() {
        return $this->belongsTo(Portal::class);
    }

    public function portalContracts() {
        return $this->hasMany(PortalContract::class);
    }


     // Accessor для title
     public function getTitleAttribute($value)
     {
         // Возвращаем пользовательский title, если он есть
         return $this->attributes['name'] ?? $this->contract->name;
     }
 
     // Accessor для template
     public function getTemplateAttribute($value)
     {
         // Возвращаем пользовательский template, если он есть
         return $this->attributes['shortName'] ?? $this->contract->shortName;
     }
 
     // Accessor для order
     public function getOrderAttribute($value)
     {
         // Возвращаем пользовательский order, если он есть
         return $this->attributes['fullName'] ?? $this->contract->fullName;
     }
}
