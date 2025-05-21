<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ai extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider',
        'activity_id',
        'file_id',
        'in_comment',
        'status',
        'result',
        'symbols_count',
        'tokens_count',
        'price',
        'domain',
        'user_id',
        'user_name',
        'entity_type',
        'entity_id',
        'entity_name',
        'user_result',
        'user_comment',
        'owner_comment',
        'user_mark',
        'owner_mark',
        'app',
        'department',
        'type',
        'model',
        'portal_id',
        'transcription_id'
    ];

    /**
     * Get the transcription that owns the AI.
     */
    public function transcription(): BelongsTo
    {
        return $this->belongsTo(Transcription::class);
    }

    /**
     * Get the portal that owns the AI.
     */
    public function portal(): BelongsTo
    {
        return $this->belongsTo(Portal::class);
    }
}
