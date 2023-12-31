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
        'stampId', 'agentId'];

        public function agent()
        {
            return $this->belongsTo(Agent::class, 'agentId');
        }
}
