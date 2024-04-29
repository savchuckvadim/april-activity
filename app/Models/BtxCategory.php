<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BtxCategory extends Model
{
    protected $table = 'btx_categories';

    protected $fillable = [
        'entity_type', 'entity_id', 'parent_type', 'type', 'group',
        'title', 'name', 'bitrixId', 'bitrixCamelId', 'code', 'isActive'
    ];

    public function entity()
    {
        return $this->morphTo();
    }


    public static function getForm($parentId, $parentType)
    {
        $btxParent = Bitrixlist::find($parentId);
        $parentClass = null;
        if ($parentType === 'smart') {
            $parentClass = Smart::class;
            $btxParent = Smart::find($parentId);
        }


        return [
            'apiName' => 'btx_category',
            'title' => 'Воронки',
            'entityType' => 'entity',
            'groups' => [
                [
                    'groupName' => 'Воронки',
                    'entityType' => 'group',
                    'isCanAddField' => true,
                    'isCanDeleteField' => true,
                    'entityType' => 'group',
                    'fields' => [

                        [
                            'id' => 0,
                            'title' => 'Отображаемое Воронки',
                            'entityType' => 'btx_category',
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
                            'title' => 'Тип Воронки (sales | service или cold | base)',
                            'name' => 'type',
                            'apiName' => 'type',
                            'type' =>  'string',
                            'entityType' => 'btx_category',

                        ],
                        [
                            'id' => 2,
                            'title' => 'group Отдел (sales | service)',
                            'name' => 'group',
                            'apiName' => 'group',
                            'type' =>  'string',
                            'entityType' => 'btx_category',

                        ],
                        [
                            'id' => 3,
                            'title' => 'имя Воронки(Категории) в битрикс',
                            'entityType' => 'btx_category',
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
                            'title' => 'id сущности родителя, тип родителя определяется на сервере',
                            'entityType' => 'btx_category',
                            'name' => 'entity_id',
                            'apiName' => 'entity_id',
                            'type' =>  'select',
                            'validation' => 'required|max:255',
                            'initialValue' => $parentId,
                            'value' => $parentId,
                            'isCanAddField' => false,
                            'isRequired' => true, //хотя бы одно поле в шаблоне должно быть
                            'items' => [
                                $btxParent
                            ]

                        ],

                        [
                            'id' => 4,
                            'title' => 'принадлежность Воронки(Категории) к родительской модели (у сделок: sales | service или у смартов: cold | base)',
                            'entityType' => 'btx_category',
                            'name' => 'parent_type',
                            'apiName' => 'parent_type',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => 'logo',
                            'isCanAddField' => false,
                            'isRequired' => true, //хотя бы одно поле в шаблоне должно быть

                        ],


                        [
                            'id' => 5,
                            'title' => 'CODE кодовое название воронки',
                            'entityType' => 'btx_category',
                            'name' => 'code',
                            'apiName' => 'code',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',

                            'isCanAddField' => false,

                        ],
                        [
                            'id' => 6,
                            'title' => 'bitrixId',
                            'entityType' => 'btx_category',
                            'name' => 'bitrixId',
                            'apiName' => 'bitrixId',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',

                            'isCanAddField' => false,

                        ],
                        [
                            'id' => 7,
                            'title' => 'bitrixCamelId',
                            'entityType' => 'btx_category',
                            'name' => 'bitrixCamelId',
                            'apiName' => 'bitrixCamelId',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',

                            'isCanAddField' => false,

                        ],
                        [
                            'id' => 8,
                            'title' => 'Тип класса родителя - чтобы контроллер от этого условия определил нужную модель родителя | deal |  lead | task | smart',
                            'entityType' => 'btx_category',
                            'name' => 'entity_type',
                            'apiName' => 'entity_type',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',
                            'value' => '',

                            'isCanAddField' => false,

                        ],
                        [
                            'id' => 8,
                            'title' => 'isActive',
                            'entityType' => 'btx_category',
                            'name' => 'isActive',
                            'apiName' => 'isActive',
                            'type' =>  'boolean',
                            'validation' => 'required|max:255',
                            'initialValue' => 'true',
                            'value' => 'true',

                            'isCanAddField' => false,

                        ],

                    ],

                    'relations' => [],

                ]
            ]
        ];
    }
}
