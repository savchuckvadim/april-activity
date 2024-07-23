<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;
    // protected $with = ['measure', 'portalContracts.portal'];

    protected $fillable = ['name', 'number', 'title', 'code', 'type', 'template', 'order', 'coefficient', 'prepayment', 'discount', 'productName', 'withPrepayment'];

    // Отношение к PortalContract через связующую таблицу
    public function portalContracts()
    {
        return $this->hasMany(PortalContract::class);
    }

    public static function getForm()
    {

        return [
            'apiName' => 'contract',
            'title' => 'Создание contract - обобщающая модель',
            'entityType' => 'entity',
            'groups' => [
                [
                    'groupName' => 'contract - обобщающая модель',
                    'entityType' => 'group',
                    'isCanAddField' => true,
                    'isCanDeleteField' => true,
                    'entityType' => 'group',
                    'fields' => [
                        [
                            'id' => 0,
                            'title' => 'title',
                            'entityType' => 'contract',
                            'name' => 'title',
                            'apiName' => 'title',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',
                            'isCanAddField' => false,
                            'isRequired' => true, //хотя бы одно поле в шаблоне должно быть

                        ],
                        [
                            'id' => 1,
                            'title' => 'order',
                            'entityType' => 'contract',
                            'name' => 'order',
                            'apiName' => 'order',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',
                            'isCanAddField' => false,
                            'isRequired' => true, //хотя бы одно поле в шаблоне должно быть

                        ],

                        [
                            'id' => 2,
                            'title' => 'name ',
                            'entityType' => 'contract',
                            'name' => 'name',
                            'apiName' => 'name',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',
                            'isCanAddField' => false,
                            'isRequired' => true, //хотя бы одно поле в шаблоне должно быть

                        ],

                        [
                            'id' => 3,
                            'title' => 'code',
                            'entityType' => 'contract',
                            'name' => 'code',
                            'apiName' => 'code',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',

                            'isCanAddField' => false,

                        ],

                        [
                            'id' => 4,
                            'title' => 'type',
                            'entityType' => 'contract',
                            'name' => 'type',
                            'apiName' => 'type',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',
                            'isCanAddField' => false,

                        ],

                        [
                            'id' => 5,
                            'title' => 'number',
                            'entityType' => 'contract',
                            'name' => 'number',
                            'apiName' => 'number',
                            'type' =>  'number',
                            'validation' => 'required|max:255',
                            'initialValue' => '',
                            'isCanAddField' => false,

                        ],
                        [
                            'id' => 6,
                            'title' => 'coefficient',
                            'entityType' => 'contract',
                            'name' => 'coefficient',
                            'apiName' => 'coefficient',
                            'type' =>  'number',
                            'validation' => 'required|max:255',
                            'initialValue' => '',
                            'isCanAddField' => false,

                        ],
                        [
                            'id' => 7,
                            'title' => 'prepayment',
                            'entityType' => 'contract',
                            'name' => 'prepayment',
                            'apiName' => 'prepayment',
                            'type' =>  'number',
                            'validation' => 'required|max:255',
                            'initialValue' => '',
                            'isCanAddField' => false,

                        ],
                        [
                            'id' => 8,
                            'title' => 'template',
                            'entityType' => 'contract',
                            'name' => 'template',
                            'apiName' => 'template',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',
                            'isCanAddField' => false,

                        ],
                        [
                            'id' => 9,
                            'title' => 'discount',
                            'entityType' => 'contract',
                            'name' => 'discount',
                            'apiName' => 'discount',
                            'type' =>  'number',
                            'validation' => 'required|max:255',
                            'initialValue' => '',
                            'value' => 1,
                            'isCanAddField' => false,

                        ],
                        [
                            'id' => 10,
                            'title' => 'productName',
                            'entityType' => 'contract',
                            'name' => 'productName',
                            'apiName' => 'productName',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',
                            'isCanAddField' => false,

                        ],
                        [
                            'id' => 11,
                            'title' => 'product',
                            'entityType' => 'contract',
                            'name' => 'product',
                            'apiName' => 'product',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',
                            'isCanAddField' => false,

                        ],
                        [
                            'id' => 11,
                            'title' => 'service',
                            'entityType' => 'contract',
                            'name' => 'service',
                            'apiName' => 'service',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',
                            'isCanAddField' => false,

                        ],
                       
                        [
                            'id' => 12,
                            'title' => 'withPrepayment',
                            'entityType' => 'contract',
                            'name' => 'withPrepayment',
                            'apiName' => 'withPrepayment',
                            'type' =>  'boolean',
                            'validation' => '',
                            'initialValue' => '',
                            'isCanAddField' => false,

                        ],

                    ],

                    'relations' => [],

                ]
            ]
        ];
    }
}
