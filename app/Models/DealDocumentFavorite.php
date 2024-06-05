<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DealDocumentFavorite extends Model
{
    use HasFactory;
    protected $fillable = [
        'dealId',
        'dealDocumentOptionId',
        'domain',
        'btxUserId',
        'title',
        'complectName',
        'dealName',
        'description',
        'settings',
        'tag',
        'type',
        'group',
        'promotionName',
        'promotionCode',
        'targetAudience',
    ];

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
