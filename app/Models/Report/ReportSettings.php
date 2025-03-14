<?php

namespace App\Models\Report;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportSettings extends Model
{
    use HasFactory;
    protected $fillable = [
        'domain',
        'portalId',
        'bxUserId',
        'filter',
        'filters',
        'grafics',
        'department',
        'date',
        'dates',
        'other',
    ];
}
