<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Counter extends Model
{
    use HasFactory;

    public function templates()
    {
        return $this->belongsToMany(Template::class, 'template_counter')
            ->withPivot('value', 'prefix', 'day', 'year', 'month', 'count', 'size');
    }

    public static function getForm()
    {

        return [
            'apiName' => 'counter',
            'title' => 'Создание counter',
            'entityType' => 'entity',
            'groups' => [
                [
                    'groupName' => 'Поля для Создания counter',
                    'entityType' => 'group',
                    'isCanAddField' => true,
                    'isCanDeleteField' => true,
                    'entityType' => 'group',
                    'fields' => [



                        [
                            'id' => 1,
                            'title' => 'value',
                            'name' => 'value',
                            'apiName' => 'value',
                            'type' =>  'string',
                            'entityType' => 'counter',
                            'validation' => 'required|max:255',
                            'initialValue' => '',
                            'isCanAddField' => false,
                            'isRequired' => true, //хотя бы одно поле в шаблоне должно быть
                        ],

                        [
                            'id' => 2,
                            'title' => 'prefix',
                            'name' => 'prefix',
                            'apiName' => 'prefix',
                            'type' =>  'string',
                            'entityType' => 'counter',
                            'validation' => 'required|max:255',
                            'initialValue' => '',
                            'isCanAddField' => false,
                            'isRequired' => true, //хотя бы одно поле в шаблоне должно быть
                        ],

                        [
                            'id' => 3,
                            'title' => 'name',
                            'entityType' => 'counter',
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
                            'entityType' => 'counter',
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
                            'title' => 'size',
                            'entityType' => 'counter',
                            'name' => 'size',
                            'apiName' => 'size',
                            'type' =>  'number',
                            'validation' => 'required|max:255',
                            'initialValue' => '',

                            'isCanAddField' => false,

                        ],
                        [
                            'id' => 6,
                            'title' => 'count',
                            'entityType' => 'counter',
                            'name' => 'count',
                            'apiName' => 'count',
                            'type' =>  'number',
                            'validation' => 'required|max:255',
                            'initialValue' => '',

                            'isCanAddField' => false,

                        ],
                        [
                            'id' => 7,
                            'title' => 'day',
                            'entityType' => 'counter',
                            'name' => 'day',
                            'apiName' => 'day',
                            'type' =>  'boolean',
                            'validation' => '',
                            'initialValue' => false,

                            'isCanAddField' => false,

                        ],
                        [
                            'id' => 8,
                            'title' => 'month',
                            'entityType' => 'counter',
                            'name' => 'month',
                            'apiName' => 'month',
                            'type' =>  'boolean',
                            'validation' => '',
                            'initialValue' => false,

                            'isCanAddField' => false,

                        ],
                        [
                            'id' => 9,
                            'title' => 'year',
                            'entityType' => 'counter',
                            'name' => 'year',
                            'apiName' => 'year',
                            'type' =>  'boolean',
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
