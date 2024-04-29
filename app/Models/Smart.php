<?php

namespace App\Models;

use App\Http\Controllers\PortalController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Smart extends Model
{
    use HasFactory;
    // public $timestamps = false;

    protected $fillable = [
        'type',
        'group',
        'name',
        'title',
        'bitrixId',
        'entityTypeId', //134
        'forStageId', //134
        'forFilterId',  //134
        'crmId',  //134_
        'forStage', //DT134_
        'forFilter',  //DYNAMIC_134_  
        'crm',  //T9c_
        'portal_id'

    ];

    public function portal()
    {
        return $this->belongsTo(Portal::class);
    }

    public function categories()
    {
        return $this->morphMany(BtxCategory::class, 'entity');
    }

    public function fields()
    {
        return $this->morphMany(Bitrixfield::class, 'entity')->where('parent_type', 'smart');
    }







    public static function getForm($portalId)
    {

        $portalsSelect = PortalController::getSelectPortals($portalId);

        return [
            'apiName' => 'smart',
            'title' => 'Создание Смарт Процесса',
            'entityType' => 'entity',
            'groups' => [
                [
                    'groupName' => 'Поля Создания Смарт Процесса',
                    'entityType' => 'group',
                    'isCanAddField' => true,
                    'isCanDeleteField' => true,
                    'entityType' => 'group',
                    'fields' => [



                        [
                            'id' => 1,
                            'title' => 'Тип группы звоноков (sales, service, seminars)',
                            'name' => 'type',
                            'apiName' => 'type',
                            'entityType' => 'smart',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',
                            'isCanAddField' => false,
                            'isRequired' => true, //хотя бы одно поле в шаблоне должно быть
                        ],

                        [
                            'id' => 2,
                            'title' => 'К какой группе относится (sales, service)',
                            'name' => 'group',
                            'apiName' => 'group',
                            'entityType' => 'smart',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',
                            'isCanAddField' => false,
                            'isRequired' => true, //хотя бы одно поле в шаблоне должно быть
                        ],

                        [
                            'id' => 3,
                            'title' => 'name',
                            'entityType' => 'smart',
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
                            'entityType' => 'smart',
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
                            'title' => 'ID в битриксе !',
                            'entityType' => 'smart',
                            'name' => 'bitrixId',
                            'apiName' => 'bitrixId',
                            'type' =>  'number',
                            'validation' => 'required|max:255',
                            'initialValue' => '',

                            'isCanAddField' => false,

                        ],
                        [
                            'id' => 6,
                            'title' => 'entityTypeId //134 ',
                            'entityType' => 'smart',
                            'name' => 'entityTypeId',
                            'apiName' => 'entityTypeId',
                            'type' =>  'number',
                            'validation' => 'required|max:255',
                            'initialValue' => '134',

                            'isCanAddField' => false,

                        ],
                        [
                            'id' => 7,
                            'title' => 'forStageId 134',
                            'entityType' => 'smart',
                            'name' => 'forStageId',
                            'apiName' => 'forStageId',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',

                            'isCanAddField' => false,

                        ],
                        [
                            'id' => 8,
                            'title' => 'forFilterId  134',
                            'entityType' => 'smart',
                            'name' => 'forFilterId',
                            'apiName' => 'forFilterId',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',

                            'isCanAddField' => false,

                        ],
                        [
                            'id' => 8,
                            'title' => 'crmId  134',
                            'entityType' => 'smart',
                            'name' => 'crmId',
                            'apiName' => 'crmId',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',

                            'isCanAddField' => false,

                        ],

                        [
                            'id' => 9,
                            'title' => 'forStage  //DT134_',
                            'entityType' => 'smart',
                            'name' => 'forStage',
                            'apiName' => 'forStage',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',

                            'isCanAddField' => false,

                        ],
                        [
                            'id' => 8,
                            'title' => 'forFilter  // DYNAMIC_134_',
                            'entityType' => 'smart',
                            'name' => 'forFilter',
                            'apiName' => 'forFilter',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',

                            'isCanAddField' => false,

                        ],
                        [
                            'id' => 8,
                            'title' => 'crm  // T9c',
                            'entityType' => 'smart',
                            'name' => 'crm',
                            'apiName' => 'crm',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',

                            'isCanAddField' => false,

                        ],

                        [
                            'id' => 9,
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
