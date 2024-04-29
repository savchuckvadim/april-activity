<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bitrixfield extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'name',
        'code',
        'type',
        'bitrixId',
        'bitrixCamelId',
        'entity_id',
        'entity_type',
        'parent_type', //название типа филда в родительской модели напр list или dealProduct dealComplect - к чему относится field

    ];

    public function entity()
    {
        return $this->morphTo();
    }

    public function items()
    {
        return $this->hasMany(BitrixfieldItem::class);
    }



    
    public static function getForm($parentId)
    {
        $btxListParent = Bitrixlist::find($parentId);

        return [
            'apiName' => 'bitrixlistfield',
            'title' => 'Загрузка файла',
            'entityType' => 'entity',
            'groups' => [
                [
                    'groupName' => 'Филды для Списков, Компаний',
                    'entityType' => 'group',
                    'isCanAddField' => true,
                    'isCanDeleteField' => true,
                    'entityType' => 'group',
                    'fields' => [

                        [
                            'id' => 0,
                            'title' => 'Отображаемое имя филда',
                            'entityType' => 'bitrixlistfield',
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
                            'title' => 'Тип филда (select, date, string)',
                            'name' => 'type',
                            'apiName' => 'type',
                            'type' =>  'string',
                            'entityType' => 'bitrixlistfield',

                        ],

                        [
                            'id' => 3,
                            'title' => 'имя филда в битрикс',
                            'entityType' => 'bitrixlistfield',
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
                            'entityType' => 'bitrixlistfield',
                            'name' => 'entity_id',
                            'apiName' => 'entity_id',
                            'type' =>  'select',
                            'validation' => 'required|max:255',
                            'initialValue' => $parentId,
                            'value' => $parentId,
                            'isCanAddField' => false,
                            'isRequired' => true, //хотя бы одно поле в шаблоне должно быть
                            'items' => [
                                $btxListParent
                            ]

                        ],

                        [
                            'id' => 4,
                            'title' => 'принадлежность филда к родительской модели list complectField для доступа из родителя к определенного типа филдам в сделках - только для товаров например',
                            'entityType' => 'bitrixlistfield',
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
                            'title' => 'Код для ассоциаций должен совпадать с битрикс CODE поля',
                            'entityType' => 'bitrixlistfield',
                            'name' => 'code',
                            'apiName' => 'code',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',

                            'isCanAddField' => false,

                        ],
                        [
                            'id' => 6,
                            'title' => 'bitrixId UF_CRM',
                            'entityType' => 'bitrixlistfield',
                            'name' => 'bitrixId',
                            'apiName' => 'bitrixId',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',

                            'isCanAddField' => false,

                        ],
                        [
                            'id' => 7,
                            'title' => 'bitrixCamelId ufCrm',
                            'entityType' => 'bitrixlistfield',
                            'name' => 'bitrixCamelId',
                            'apiName' => 'bitrixCamelId',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',

                            'isCanAddField' => false,

                        ],
                        [
                            'id' => 8,
                            'title' => 'тип родителя - чтобы контроллер от этого условия определил нужную модель родителя list | deal | company | lead | task | smart',
                            'entityType' => 'bitrixlistfield',
                            'name' => 'parent model short name',
                            'apiName' => 'entityType',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
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
