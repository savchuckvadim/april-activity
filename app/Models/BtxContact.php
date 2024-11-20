<?php

namespace App\Models;

use App\Http\Controllers\PortalController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BtxContact extends Model
{
    use HasFactory;
    protected $with = ['bitrixfields']; 

    public function portal()
    {
        return $this->belongsTo(Portal::class);
    }

    // public function categories()
    // {
    //     return $this->morphMany(BtxCategory::class, 'entity');
    // }

    public function bitrixfields()
    {
        return $this->morphMany(Bitrixfield::class, 'entity');
    }


    public static function getForm($portalId)
    {

        $portalsSelect = PortalController::getSelectPortals($portalId);

        return [
            'apiName' => 'contact',
            'title' => 'Создание CONTACT - обобщающая модель',
            'entityType' => 'entity',
            'groups' => [
                [
                    'groupName' => 'contact - обобщающая модель',
                    'entityType' => 'group',
                    'isCanAddField' => true,
                    'isCanDeleteField' => true,
                    'entityType' => 'group',
                    'fields' => [
                        [
                            'id' => 0,
                            'title' => 'name',
                            'entityType' => 'contact',
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
                            'title' => 'Show Name (title)',
                            'entityType' => 'contact',
                            'name' => 'title',
                            'apiName' => 'title',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',
                            'isCanAddField' => false,
                            'isRequired' => true, //хотя бы одно поле в шаблоне должно быть

                        ],

                        [
                            'id' => 2,
                            'title' => 'code',
                            'entityType' => 'contact',
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
                            'entityType' => 'contact',
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
