<?php

namespace App\Models;

use App\Http\Controllers\Admin\AgentController;
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

        
        'personName',
        'document',
        'docSer',
        'docNum',
        'docDate',


        'docIssuedBy',
        'docDepCode',
        'registredAdress',
        'primaryAdresss',
        'email', 
        'garantEmail',
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

    public function counters()
    {
        return $this->belongsToMany(Counter::class, 'rq_counter')
            ->withPivot('value', 'type',  'prefix', 'postfix', 'day', 'year', 'month', 'count', 'size');
    }

    public static function getForm($agentId)
    {

        $agentsSelect = AgentController::getProvider($agentId);
        $fillable = [
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
        $items = [];
        foreach ($fillable as $key => $fill) {
            $item = [
                'id' => $key,
                'title' => $fill,
                'entityType' => 'rq',
                'name' => $fill,
                'apiName' => $fill,
                'type' =>  'string',
                'validation' => 'required|max:255',
                'initialValue' => '',
                'isCanAddField' => false,
                'isRequired' => true, //хотя бы одно поле в шаблоне должно быть

            ];
            array_push($items, $item);
        }

        $relationitem =  [
            'id' => 20,
            'title' => 'Relation agentId',
            'entityType' => 'rq',
            'name' => 'agentId',
            'apiName' => 'agentId',
            'type' =>  'select',
            'validation' => 'required',
            'initialValue' => $agentId,
            'items' => $agentsSelect,
            'isCanAddField' => false,

        ];
        array_push($items, $relationitem);
        return [
            'apiName' => 'rq',
            'title' => 'реквизиты',
            'entityType' => 'entity',
            'groups' => [
                [
                    'groupName' => 'rq',
                    'entityType' => 'group',
                    'isCanAddField' => true,
                    'isCanDeleteField' => true,
                    'entityType' => 'group',
                    'fields' => $items,

                    'relations' => [],

                ]
            ]
        ];
    }
}
