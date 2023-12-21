<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'type',
        'link',
        'portalId',

    ];

    // public function providers()
    // {
    //     return $this->hasMany(TField::class);
    // }


    public static function getTemplate($templateId)
    {
        return Template::find($templateId);
    }

    public static function getTemplatePath($templateId)
    {
        $templatePath = null;

        $template = Template::find($templateId);
        if ($template) {
            $templatePath = $template->link;
        }
        return $templatePath;
    }

    public function fields()
    {
        return $this->belongsToMany(Field::class, 'template_field', 'template_id', 'field_id');
    }

    public function portal()
    {
        return $this->belongsTo(Portal::class, 'portalId', 'id');
    }


    public static function getForm()
    {

        return [
            [
                'groupName' => 'Поля Шаблона',
                'type' => 'template',
                'isCanAddField' => true,
                'isCanDeleteField' => true,
                'fieldGroups' => [

                    [
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
                            'type' =>  'boolean',
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
                    ]
                ],
                'isRequired' => true //хотя бы одно поле в шаблоне должно быть
            ],
            [
                'groupName' => 'Портал и реквизиты',
                'type' => 'template',
                'isCanAddField' => false,
                'isCanDeleteField' => false,
                'relations' => [

                    [
                        'title' => 'Домен',
                        'name' => 'domain',
                        'apiName' => 'domain',
                        'type' =>  'selectApi',
                        'getSelect' => 'portals',
                        'validation' => 'required|max:255',
                        'initialValue' => '',
                        'templatePlace' => 1,
                        'isCanAddField' => false,

                    ],
                    [
                        'title' => 'Реквизиты',
                        'name' => 'domain',
                        'apiName' => 'domain',
                        'type' =>  'selectApi',
                        'getSelect' => 'rqs',
                        'validation' => 'required|max:255',
                        'initialValue' => '',
                        'dependOf' => ['domain'],
                        'templatePlace' => 1,
                        'isCanAddField' => false,

                    ],

                ]
            ],
        ];
    }
}
