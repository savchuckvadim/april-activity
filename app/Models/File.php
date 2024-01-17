<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'code',
        'type',
        'path',
        'parent',
        'parent_type', //название файла в родительской модели напр logo
        'availability'

    ];
    public function entity()
    {
        return $this->morphTo();
    }


    public static function getForm()
    {

        return [
            'apiName' => 'file',
            'title' => 'Загрузка файла',
            'entityType' => 'entity',
            'groups' => [
                [
                    'groupName' => 'Поля для загрузки файла',
                    'entityType' => 'group',
                    'isCanAddField' => true,
                    'isCanDeleteField' => true,
                    'entityType' => 'group',
                    'fields' => [

                        [
                            'id' => 0,
                            'title' => 'Название файла',
                            'entityType' => 'file',
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
                            'title' => 'Тип файла (table | video | word | img)',
                            'name' => 'type',
                            'apiName' => 'type',
                            'type' =>  'select',
                            'entityType' => 'file',
                            'items' => [
                                [
                                    'id' => 0,
                                    'title' => 'table',
                                    'name'  => 'table',
                                    'value' => 'table',
                                ],
                                [
                                    'id' => 1,
                                    'title' => 'video',
                                    'name'  => 'video',
                                    'value' => 'video',
                                ],
                                [
                                    'id' => 2,
                                    'title' => 'word',
                                    'name'  => 'word',
                                    'value' => 'word',
                                ],
                                [
                                    'id' => 3,
                                    'title' => 'img',
                                    'name'  => 'img',
                                    'value' => 'img',
                                ],

                            ]
                        ],

                        [
                            'id' => 2,
                            'title' => 'Доступность',
                            'name' => 'availability',
                            'apiName' => 'availability',
                            'type' =>  'select',
                            'entityType' => 'file',
                            'items' => [
                                [
                                    'id' => 0,
                                    'title' => 'public',
                                    'name'  => 'public',
                                    'value' => 'public',
                                ],
                                [
                                    'id' => 1,
                                    'title' => 'local',
                                    'name'  => 'local',
                                    'value' => 'local',
                                ],


                            ],
                        ],
                        [
                            'id' => 3,
                            'title' => 'Родительская модель (rq)',
                            'entityType' => 'file',
                            'name' => 'parent',
                            'apiName' => 'parent',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => 'rq',
                            'isCanAddField' => false,
                            'isRequired' => true, //хотя бы одно поле в шаблоне должно быть

                        ],
                        [
                            'id' => 4,
                            'title' => 'Название файла в родительской модели logo stamp',
                            'entityType' => 'file',
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
                            'title' => 'Код для ассоциаций',
                            'entityType' => 'field',
                            'name' => 'code',
                            'apiName' => 'code',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',
    
                            'isCanAddField' => false,
    
                        ],
                        [
                            'id' => 6,
                            'title' => 'Файл',
                            'entityType' => 'file',
                            'name' => 'file',
                            'apiName' => 'file',
                            'type' =>  'img',
                            'validation' => '',
                            'initialValue' => false,
                            'isCanAddField' => false,
                        ],
                 
                        

                    ],
                    'relations' => [],

                ]
            ]
        ];
    }
}
