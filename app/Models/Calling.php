<?php

namespace App\Models;

use App\Http\Controllers\PortalController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Calling extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = ['id', 'type', 'group', 'name', 'title', 'bitrixId', 'portal_id'];

    public function portal()
    {
        return $this->belongsTo(Portal::class);
    }

    public static function getForm($portalId)
    {
        $portalsSelect = PortalController::getSelectPortals($portalId);


        return [
            'apiName' => 'callingGroup',
            'title' => 'Создание группы звонков',
            'entityType' => 'entity',
            'groups' => [
                [
                    'groupName' => 'Поля для Создания группы звонков',
                    'entityType' => 'group',
                    'isCanAddField' => true,
                    'isCanDeleteField' => true,
                    'entityType' => 'group',
                    'fields' => [



                        [
                            'id' => 1,
                            'title' => 'Тип группы звоноков (sales, service)',
                            'name' => 'type',
                            'apiName' => 'type',
                            'type' =>  'string',
                            'entityType' => 'callingGroups',
                            'validation' => 'required|max:255',
                            'initialValue' => '',
                            'isCanAddField' => false,
                            'isRequired' => true, //хотя бы одно поле в шаблоне должно быть
                        ],

                        [
                            'id' => 2,
                            'title' => 'К какой группе относится',
                            'name' => 'group',
                            'apiName' => 'group',
                            'type' =>  'string',
                            'entityType' => 'callingGroups',
                            'validation' => 'required|max:255',
                            'initialValue' => '',
                            'isCanAddField' => false,
                            'isRequired' => true, //хотя бы одно поле в шаблоне должно быть
                        ],

                        [
                            'id' => 3,
                            'title' => 'name',
                            'entityType' => 'callingGroups',
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
                            'entityType' => 'callingGroups',
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
                            'entityType' => 'callingGroups',
                            'name' => 'bitrixId',
                            'apiName' => 'bitrixId',
                            'type' =>  'number',
                            'validation' => 'required|max:255',
                            'initialValue' => '',

                            'isCanAddField' => false,

                        ],
                        [
                            'id' => 6,
                            'title' => 'Relation portal_id',
                            'entityType' => 'callingGroups',
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
