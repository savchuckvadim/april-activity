<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rq extends Model
{
    use HasFactory;
    protected $fillable = [
        'number',
        'name',
        'type',
        'fullname',
        'shortname',
        'director',
        'position',
        'accountant',
        'based',
        'inn',
        'kpp',
        'ogrn',
        'ogrnip',
        'personName',
        'document',
        'docSer',
        'docNum',
        'docDate',
        'docIssuedBy',
        'docDepCode',
        'registredAdress',
        'primaryAdresss',
        'email', 'garantEmail',
        'phone',
        'assigned',
        'assignedPhone',
        'other',
        'bank',
        'bik',
        'rs',
        'ks',
        'bankAdress',
        'bankOther',
        'directorSignatureId',
        'accountantSignatureId',
        'stampId', 'agentId'
    ];

    public function agent()
    {
        return $this->hasOne(Agent::class, 'number', 'agentId');
    }

    public function logos()
    {
        return $this->morphMany(File::class, 'entity')->where('parent_type', 'logo');
    }

    public function stamps()
    {
        return $this->morphMany(File::class, 'entity')->where('parent_type', 'stamp');
    }

    public function signatures()
    {
        return $this->morphMany(File::class, 'entity')->where('parent_type', 'signature');
    }

    public function qrs()
    {
        return $this->morphMany(File::class, 'entity')->where('parent_type', 'qr');
    }
}
