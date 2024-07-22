<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortalContract extends Model
{
    use HasFactory;

    protected $with = ['contract', 'portalMeasure'];


    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function portal()
    {
        return $this->belongsTo(Portal::class);
    }

    public function portalMeasure()
    {
        return $this->belongsTo(PortalMeasure::class);
    }


    // Accessor для title
    public function getTitleAttribute($value)
    {
        // Возвращаем пользовательский title, если он есть
        return $this->attributes['title'] ?? $this->contract->title;
    }

    // Accessor для template
    public function getTemplateAttribute($value)
    {
        // Возвращаем пользовательский template, если он есть
        return $this->attributes['template'] ?? $this->contract->template;
    }

    // Accessor для order
    public function getOrderAttribute($value)
    {
        // Возвращаем пользовательский order, если он есть
        return $this->attributes['order'] ?? $this->contract->order;
    }
}
