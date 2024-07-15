<?php

namespace App\Models;

use App\Http\Controllers\PortalController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BtxRpa extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', //+
        'title', //+
        'code', //+
        'type', //+
        'image',
        'bitrixId', //++
        'typeId', //134

        'description', //134
        'entityTypeId',  //134
        'forStageId',  //134_
        'forFilterId', //DT134_
        'crmId',  //DYNAMIC_134_  

        'portal_id'

    ];


    protected $with = [
        'categories',
        'bitrixfields'
    ];

    public function portal()
    {
        return $this->belongsTo(Portal::class);
    }



    public function categories()
    {
        return $this->morphMany(BtxCategory::class, 'entity');
    }

    public function bitrixfields()
    {
        return $this->morphMany(Bitrixfield::class, 'entity');
    }

    public static function getForm($portalId)
    {

        $portalsSelect = PortalController::getSelectPortals($portalId);

        return [
            'apiName' => 'rpa',
            'title' => 'Создание BX RPA - обобщающая модель',
            'entityType' => 'entity',
            'groups' => [
                [
                    'groupName' => 'BX RPA - обобщающая модель',
                    'entityType' => 'group',
                    'isCanAddField' => true,
                    'isCanDeleteField' => true,
                    'fields' => [
                        [
                            'id' => 1,
                            'title' => 'Тип rpa (sales, service, seminars)',
                            'name' => 'type',
                            'apiName' => 'type',
                            'entityType' => 'rpa',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',
                            'isCanAddField' => false,
                            'isRequired' => true, //хотя бы одно поле в шаблоне должно быть
                        ],

                        [
                            'id' => 2,
                            'title' => 'RPA CODE',
                            'name' => 'code',
                            'apiName' => 'code',
                            'entityType' => 'rpa',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',
                            'isCanAddField' => false,
                            'isRequired' => true, //хотя бы одно поле в шаблоне должно быть
                        ],

                        [
                            'id' => 3,
                            'title' => 'name rpa',
                            'entityType' => 'rpa',
                            'name' => 'name',
                            'apiName' => 'name',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',
                            'isCanAddField' => false,
                            'isRequired' => true, //хотя бы одно поле в шаблоне должно быть

                        ],
                        [
                            'id' => 4,
                            'title' => 'Show Name (title)',
                            'entityType' => 'rpa',
                            'name' => 'title',
                            'apiName' => 'title',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',
                            'isCanAddField' => false,
                            'isRequired' => true, //хотя бы одно поле в шаблоне должно быть

                        ],

                        [
                            'id' => 5,
                            'title' => 'image: list, settings, math, tick, plane, piece, vacation',
                            'entityType' => 'rpa',
                            'name' => 'image',
                            'apiName' => 'image',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',
                            'isCanAddField' => false,
                            'isRequired' => true, //хотя бы одно поле в шаблоне должно быть

                        ],


                        [
                            'id' => 6,
                            'title' => 'ID в битриксе ! number',
                            'entityType' => 'rpa',
                            'name' => 'bitrixId',
                            'apiName' => 'bitrixId',
                            'type' =>  'number',
                            'validation' => 'required|max:255',
                            'initialValue' => '',

                            'isCanAddField' => false,

                        ],

                        [
                            'id' => 7,
                            'title' => 'typeId //134  string',
                            'entityType' => 'rpa',
                            'name' => 'typeId',
                            'apiName' => 'typeId',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '134',

                            'isCanAddField' => false,

                        ],
                        [
                            'id' => 8,
                            'title' => 'description ',
                            'entityType' => 'rpa',
                            'name' => 'description',
                            'apiName' => 'description',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '134',

                            'isCanAddField' => false,

                        ],
                        [
                            'id' => 9,
                            'title' => 'entityTypeId //134 number ',
                            'entityType' => 'rpa',
                            'name' => 'entityTypeId',
                            'apiName' => 'entityTypeId',
                            'type' =>  'number',
                            'validation' => 'required|max:255',
                            'initialValue' => '134',

                            'isCanAddField' => false,

                        ],
                        [
                            'id' => 10,
                            'title' => 'forStageId number',
                            'entityType' => 'rpa',
                            'name' => 'forStageId',
                            'apiName' => 'forStageId',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',

                            'isCanAddField' => false,

                        ],
                        [
                            'id' => 11,
                            'title' => 'forFilterId  134 number',
                            'entityType' => 'rpa',
                            'name' => 'forFilterId',
                            'apiName' => 'forFilterId',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',

                            'isCanAddField' => false,

                        ],
                        [
                            'id' => 12,
                            'title' => 'crmId  134 number',
                            'entityType' => 'rpa',
                            'name' => 'crmId',
                            'apiName' => 'crmId',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',

                            'isCanAddField' => false,

                        ],



                        [
                            'id' => 13,
                            'title' => 'Relation portal_id',
                            'entityType' => 'smart',
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
