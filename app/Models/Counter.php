<?php

namespace App\Models;

use App\Http\Controllers\RqController;
use App\Http\Controllers\TemplateController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Counter extends Model
{
    use HasFactory;

    public function templates()
    {
        return $this->belongsToMany(Template::class, 'template_counter')
            ->withPivot('value',  'prefix', 'day', 'year', 'month', 'count', 'size');
    }

    public function rqs()
    {
        return $this->belongsToMany(Rq::class, 'rq_counter')
            ->withPivot('value', 'type',  'prefix', 'postfix', 'day', 'year', 'month', 'count', 'size');
    }


    public static function getForm($rqId = null)
    {

        $rqSelect = RqController::getSelectRqs($rqId);
        $initialValue = null;
        if ($rqSelect && count($rqSelect) > 0) {
            $initialValue = $rqSelect[0];
        }
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
                            'title' => 'type',
                            'name' => 'type',
                            'apiName' => 'type',
                            'type' =>  'string',
                            'entityType' => 'counter',
                            'validation' => 'required|max:255',
                            'initialValue' => '',
                            'isCanAddField' => false,
                            'isRequired' => true, //хотя бы одно поле в шаблоне должно быть
                        ],

                        [
                            'id' => 3,
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
                            'id' => 4,
                            'title' => 'postfix',
                            'name' => 'postfix',
                            'apiName' => 'postfix',
                            'type' =>  'string',
                            'entityType' => 'counter',
                            'validation' => 'required|max:255',
                            'initialValue' => '',
                            'isCanAddField' => false,
                            'isRequired' => true, //хотя бы одно поле в шаблоне должно быть
                        ],

                        [
                            'id' => 5,
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
                            'id' => 6,
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
                            'id' => 7,
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
                            'id' => 8,
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
                            'id' => 9,
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
                            'id' => 10,
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
                            'id' => 11,
                            'title' => 'year',
                            'entityType' => 'counter',
                            'name' => 'year',
                            'apiName' => 'year',
                            'type' =>  'boolean',
                            'validation' => '',
                            'initialValue' => false,

                            'isCanAddField' => false,

                        ],
                        [
                            'id' => 12,
                            'title' => 'Relation template_id',
                            'entityType' => 'counter',
                            'name' => 'rq_id',
                            'apiName' => 'rq_id',
                            'type' =>  'select',
                            'validation' => 'required',
                            'initialValue' => $initialValue,
                            'items' => $rqSelect,
                            'isCanAddField' => false,

                        ],
                


                    ],

                    'relations' => [],

                ]
            ]
        ];
    }
}
