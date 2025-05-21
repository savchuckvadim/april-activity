<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transcription extends Model
{
    use HasFactory;
    protected $fillable = [
        'provider',
        'activity_id',
        'file_id',
        'in_comment',
        'status',
        'text',
        'symbols_count',
        'price',
        'duration',
        'domain',
        'user_id',
        'user_name',
        'entity_type',
        'entity_id',
        'entity_name',
        'app',
        'department',
        'report_result',
        'user_result',
        'user_comment',
        'owner_comment',
        'user_mark',
        'owner_mark',
    ];

    public function portal()
    {
        return $this->belongsTo(Portal::class, 'portal_id', 'id');
    }
}
