<?php

namespace App\Models;

use App\Http\Controllers\PortalController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departament extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = ['type', 'group', 'name', 'title', 'bitrixId', 'portal_id'];

    public function portal()
    {
        return $this->belongsTo(Portal::class);
    }

    public static function getForm($portalId = null)
    {

        $portalsSelect = PortalController::getSelectPortals($portalId);

        return [
            'apiName' => 'departament',
            'title' => 'Создание departament',
            'entityType' => 'entity',
            'groups' => [
                [
                    'groupName' => 'Поля для Создания departament',
                    'entityType' => 'group',
                    'isCanAddField' => true,
                    'isCanDeleteField' => true,
                    'entityType' => 'group',
                    'fields' => [



                        [
                            'id' => 1,
                            'title' => 'Тип departament (sales, service)',
                            'name' => 'type',
                            'apiName' => 'type',
                            'type' =>  'string',
                            'entityType' => 'departament',
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
                            'entityType' => 'departament',
                            'validation' => 'required|max:255',
                            'initialValue' => '',
                            'isCanAddField' => false,
                            'isRequired' => true, //хотя бы одно поле в шаблоне должно быть
                        ],

                        [
                            'id' => 3,
                            'title' => 'name',
                            'entityType' => 'departament',
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
                            'entityType' => 'departament',
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
                            'entityType' => 'departament',
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
                            'entityType' => 'bitrixlist',
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
