<?php

namespace App\Models;

use App\Http\Controllers\PortalController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    use HasFactory;
    protected $with = ['rq'];
    protected $fillable = [
        'number', 'type',
        'portalId', 'name', 'code', 'withTax'

    ];

    public function portal()
    {
        return $this->belongsTo(Portal::class, 'portalId', 'id');
    }


    public function rq()
    {
        return $this->hasOne(Rq::class, 'agentId', 'id');
    }
    // Аксессор для получения связи `rq`
    // public function getRqAttribute($value)
    // {
    //     return $this->rq()->exists() ? $this->rq()->first() : [];
    // }

    public static function getForm($portalId)
    {

        $portalsSelect = PortalController::getSelectPortals($portalId);

        return [
            'apiName' => 'agent',
            'title' => 'Поставщик',
            'entityType' => 'entity',
            'groups' => [
                [
                    'groupName' => 'Поставщик',
                    'entityType' => 'group',
                    'isCanAddField' => true,
                    'isCanDeleteField' => true,
                    'entityType' => 'group',
                    'fields' => [
                        [
                            'id' => 0,
                            'title' => 'name',
                            'entityType' => 'agent',
                            'name' => 'name',
                            'apiName' => 'name',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',
                            'isCanAddField' => false,
                            'isRequired' => true, //хотя бы одно поле в шаблоне должно быть

                        ],
                        [
                            'id' => 1,
                            'title' => 'number',
                            'entityType' => 'agent',
                            'name' => 'number',
                            'apiName' => 'number',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',
                            'isCanAddField' => false,
                            'isRequired' => true, //хотя бы одно поле в шаблоне должно быть

                        ],
                        [
                            'id' => 1,
                            'title' => 'type',
                            'entityType' => 'agent',
                            'name' => 'type',
                            'apiName' => 'type',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',
                            'isCanAddField' => false,
                            'isRequired' => true, //хотя бы одно поле в шаблоне должно быть

                        ],

                        [
                            'id' => 2,
                            'title' => 'code',
                            'entityType' => 'agent',
                            'name' => 'code',
                            'apiName' => 'code',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',

                            'isCanAddField' => false,

                        ],
                        [
                            'id' => 3,
                            'title' => 'Relation portal_id',
                            'entityType' => 'agent',
                            'name' => 'portal_id',
                            'apiName' => 'portal_id',
                            'type' =>  'select',
                            'validation' => 'required',
                            'initialValue' => $portalId,
                            'items' => $portalsSelect,
                            'isCanAddField' => false,

                        ],





                    ],

                    'relations' => [],

                ]
            ]
        ];
    }
}
