<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Field extends Model
{
    use HasFactory;


    protected $fillable = [
        'number',
        'name',
        'code',
        'type',
        'isGeneral',
        'isDefault',
        'isRequired',
        'value',
        'description',
        'bitixId',
        'bitrixTemplateId',
        'isActive',
        'isPlural',
    ];

    public function items()
    {
        return $this->hasMany(FItem::class, 'fieldId', 'id');
    }

    public function templates()
    {
        return $this->belongsToMany(Template::class, 'template_field', 'field_id', 'template_id');
    }


        //for create

        public static function getForm()
        {
    
            return [
                [
                    'groupName' => 'Создание портала',
                    'type' => 'portal',
                    'isCanAddField' => false,
                    'isCanDeleteField' => false, //нельзя удалить ни одно из инициальзационных fields
                    'isRequired' => true, //хотя бы одно поле в шаблоне должно быть
                    'fields' => [
                        [
                            'title' => 'Название',
                            'name' => 'name',
                            'apiName' => 'name',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',

                            'isCanAddField' => false,

                        ],
                        [
                            'title' => 'Тип (string | array | integer | float | data | img)',
                            'name' => 'type',
                            'apiName' => 'type',
                            'type' =>  'select',
                            'items' => [
                                [
                                    'id' => 0,
                                    'title' => 'string',
                                    'name'  => 'string',
                                    'value' => 'string',
                                ],
                                [
                                    'id' => 1,
                                    'title' => 'array',
                                    'name'  => 'array',
                                    'value' => 'array',
                                ],
                                [
                                    'id' => 2,
                                    'title' => 'integer',
                                    'name'  => 'integer',
                                    'value' => 'integer',
                                ],
                                [
                                    'id' => 3,
                                    'title' => 'float',
                                    'name'  => 'float',
                                    'value' => 'float',
                                ],
                                [
                                    'id' => 4,
                                    'title' => 'data',
                                    'name'  => 'data',
                                    'value' => 'data',
                                ],
                                [
                                    'id' => 5,
                                    'title' => 'img',
                                    'name'  => 'img',
                                    'value' => 'img',
                                ],
                            ],
                            'validation' => 'required|max:255',
                            'initialValue' => '',
                            'isCanAddField' => false,

                        ],
                        [
                            'title' => 'Является общим для всех шаблонов',
                            'name' => 'isGeneral',
                            'apiName' => 'isGeneral',
                            'type' =>  'boolean',
                            'validation' => '',
                            'initialValue' => false,
                            'isCanAddField' => false,

                        ],
                        [
                            'title' => 'Имеет значение по умолчанию',
                            'name' => 'isDefault',
                            'apiName' => 'isDefault',
                            'type' =>  'boolean',
                            'validation' => '',
                            'initialValue' => false,
                            'isCanAddField' => false,

                        ],
                        [
                            'title' => 'Обязательно для заполнения',
                            'name' => 'isRequired',
                            'apiName' => 'isRequired',
                            'type' =>  'boolean',
                            'validation' => '',
                            'initialValue' => false,
                            'isCanAddField' => false,

                        ],
                        [
                            'title' => 'Инициализационное значение',
                            'name' => 'value',
                            'apiName' => 'value',
                            'type' =>  'string',
                            'validation' => '',
                            'initialValue' => false,
                            'isCanAddField' => false,

                        ],
                        [
                            'title' => 'Описание',
                            'name' => 'description',
                            'apiName' => 'description',
                            'type' =>  'string',
                            'validation' => '',
                            'initialValue' => '',
                            'isCanAddField' => false,
                        ],
                        [
                            'title' => 'Id в битриксе',
                            'name' => 'bitixId',
                            'apiName' => 'bitixId',
                            'type' =>  'string',
                            'validation' => '',
                            'initialValue' => '',
                            'isCanAddField' => false,
                        ],
                        [
                            'title' => 'Место в шаблоне',
                            'name' => 'bitrixTemplateId',
                            'apiName' => 'bitrixTemplateId',
                            'type' =>  'string',
                            'validation' => '',
                            'initialValue' => '',
                            'isCanAddField' => false,
                        ],
                        [
                            'title' => 'Место в шаблоне',
                            'name' => 'isActive',
                            'apiName' => 'isActive',
                            'type' =>  'string',
                            'validation' => '',
                            'initialValue' => false,
                            'isCanAddField' => false,
                        ],
                        [
                            'title' => 'Множественное поле',
                            'name' => 'isPlural',
                            'apiName' => 'isPlural',
                            'type' =>  'boolean',
                            'validation' => '',
                            'initialValue' => false,
                            'isCanAddField' => false,
                        ],
                        [
                            'title' => 'Картинка',
                            'name' => 'img',
                            'apiName' => 'img',
                            'type' =>  'img',
                            'validation' => '',
                            'initialValue' => false,
                            'isCanAddField' => false,
                        ],
                    ]
                ],
            ];
        }
}
