<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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
            'apiName' => 'field',
            'title' => 'Поля Шаблона Документа',
            'entityType' => 'entity',
            'groups' =>
            [[
                'groupName' => 'Создание портала',
                'entityType' => 'group',
                // 'type' => 'portal',
                'isCanAddField' => false,
                'isCanDeleteField' => false, //нельзя удалить ни одно из инициальзационных fields
                'isRequired' => true, //хотя бы одно поле в шаблоне должно быть
                'fields' => [
                    [
                        'title' => 'Название',
                        'entityType' => 'field',
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
                        'entityType' => 'field',
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
                                'title' => 'boolean',
                                'name'  => 'boolean',
                                'value' => 'boolean',
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
                        'entityType' => 'field',
                        'name' => 'isGeneral',
                        'apiName' => 'isGeneral',
                        'type' =>  'boolean',
                        'validation' => '',
                        'initialValue' => false,
                        'isCanAddField' => false,

                    ],
                    [
                        'title' => 'Имеет значение по умолчанию',
                        'entityType' => 'field',
                        'name' => 'isDefault',
                        'apiName' => 'isDefault',
                        'type' =>  'boolean',
                        'validation' => '',
                        'initialValue' => false,
                        'isCanAddField' => false,

                    ],
                    [
                        'title' => 'Обязательно для заполнения',
                        'entityType' => 'field',
                        'name' => 'isRequired',
                        'apiName' => 'isRequired',
                        'type' =>  'boolean',
                        'validation' => '',
                        'initialValue' => false,
                        'isCanAddField' => false,

                    ],
                    [
                        'title' => 'Инициализационное значение',
                        'entityType' => 'field',
                        'name' => 'value',
                        'apiName' => 'value',
                        'type' =>  'string',
                        'validation' => '',
                        'initialValue' => '',
                        'isCanAddField' => false,

                    ],
                    [
                        'title' => 'Описание',
                        'entityType' => 'field',
                        'name' => 'description',
                        'apiName' => 'description',
                        'type' =>  'string',
                        'validation' => '',
                        'initialValue' => '',
                        'isCanAddField' => false,
                    ],
                    [
                        'title' => 'Id в битриксе',
                        'entityType' => 'field',
                        'name' => 'bitixId',
                        'apiName' => 'bitixId',
                        'type' =>  'string',
                        'validation' => '',
                        'initialValue' => '',
                        'isCanAddField' => false,
                    ],
                    [
                        'title' => 'Место в шаблоне',
                        'entityType' => 'field',
                        'name' => 'bitrixTemplateId',
                        'apiName' => 'bitrixTemplateId',
                        'type' =>  'string',
                        'validation' => '',
                        'initialValue' => '',
                        'isCanAddField' => false,
                    ],
                    [
                        'title' => 'Поле активно ',
                        'entityType' => 'field',
                        'name' => 'isActive',
                        'apiName' => 'isActive',
                        'type' =>  'boolean',
                        'validation' => '',
                        'initialValue' => false,
                        'isCanAddField' => false,
                    ],
                    [
                        'title' => 'Множественное поле',
                        'entityType' => 'field',
                        'name' => 'isPlural',
                        'apiName' => 'isPlural',
                        'type' =>  'boolean',
                        'validation' => '',
                        'initialValue' => false,
                        'isCanAddField' => false,
                    ],
                    [
                        'title' => 'Изменяемое клиентом ?',
                        'entityType' => 'field',
                        'name' => 'isClient',
                        'apiName' => 'isClient',
                        'type' =>  'boolean',
                        'validation' => '',
                        'initialValue' => false,
                        'isCanAddField' => false,
                    ],
                    [
                        'title' => 'Картинка',
                        'entityType' => 'field',
                        'name' => 'img',
                        'apiName' => 'img',
                        'type' =>  'img',
                        'validation' => '',
                        'initialValue' => false,
                        'isCanAddField' => false,
                    ],
                ],
                'relations' => [],
            ]]
        ];
    }


    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($field) {
            // Удаляем все связи с шаблонами
            $field->templates()->detach();

            if ($field['type'] == 'img') {
                // Удаляем файл из хранилища
                $filePath = str_replace('/storage', 'public', $field->value);
                Storage::delete($filePath);
            }
        });
       
    }
}
