<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DealDocumentFavorite extends Model
{
    use HasFactory;


    // Связь с Deal
    public function deal()
    {
        return $this->belongsTo(Deal::class, 'dealId');
    }

    // Связь с DocumentOption
    public function documentOption()
    {
        return $this->belongsTo(DealDocumentOption::class, 'dealDocumentOptionId');
    }
}
