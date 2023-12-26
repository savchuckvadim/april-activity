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
            'apiName' => 'template',
            'title' => 'Шаблон Документа',
            'entityType' => 'entity',
            'groups' => [
                [
                    'groupName' => 'База',
                    'entityType' => 'group',
                    'isCanAddField' => true,
                    'isCanDeleteField' => true,
                    'entityType' => 'group',
                    'fields' => [

                        [
                            'id' => 0,
                            'title' => 'Название шаблона',
                            'entityType' => 'field',
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
                            'title' => 'Тип шаблона',
                            'entityType' => 'field',
                            'name' => 'type',
                            'apiName' => 'type',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => 'offer | invoice | contract',
                            'isCanAddField' => false,
                            'isRequired' => true, //хотя бы одно поле в шаблоне должно быть

                        ],
                    ],
                    'relations' => [],

                ],
                [
                    'groupName' => 'Поля Шаблона',
                    'entityType' => 'group',
                    // 'type' => 'entities',
                    'isCanAddField' => true,
                    'isCanDeleteField' => true,
                    // 'initialField' =>
                    // [
                    //     'id' => 0,
                    //     'title' => 'Поле шаблона',
                    //     'name' => 'field',
                    //     'apiName' => 'field',
                    //     'type' =>  'entity',   //имеет возможность создавать модели и связывать их с создаваемой сущностью
                    //     'validation' => '',
                    //     'initialValue' => Field::getForm(),
                    //     'isCanAddField' => true,
                    //     'isRequired' => true, //хотя бы одно поле в шаблоне должно быть
                    //     'fields' => [],

                    // ],
                    'relations' => [Field::getForm()],
                    'fields' => []

                ],
                [
                    'groupName' => 'Портал и реквизиты',
                    'entityType' => 'group',
                    
                    // 'type' => 'template',
                    'isCanAddField' => false,
                    'isCanDeleteField' => false,
                    'fields' => [

                        [
                            'id' => 0,
                            'title' => 'Домен',
                            'entityType' => 'field',
                            'name' => 'domain',
                            'apiName' => 'domain',
                            'type' =>  'string',
                            'getSelect' => 'portals',
                            'dependOf' => null,
                            'validation' => 'required|max:255',
                            'initialValue' => '',

                            'isCanAddField' => false,

                        ],
                        
                        // [
                        //     'title' => 'Реквизиты',
                        //     'name' => 'domain',
                        //     'apiName' => 'domain',
                        //     'type' =>  'selectApi',
                        //     'getSelect' => 'rqs',
                        //     'validation' => 'required|max:255',
                        //     'initialValue' => '',
                        //     'dependOf' => ['domain'],

                        //     'isCanAddField' => false,

                        // ],

                        ],
                        'relations' => [],
                ]
            ]
        ];
    }
}
