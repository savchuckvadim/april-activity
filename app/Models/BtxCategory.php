<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BtxCategory extends Model
{
    protected $table = 'btx_categories';

    protected $fillable = [
        'entity_type', 'entity_id', 'parent_type', 'type', 'group',
        'title', 'name', 'bitrixId', 'bitrixCamelId', 'code', 'isActive'
    ];

    public function entity()
    {
        return $this->morphTo();
    }
}
