<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Measure extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'shortName', 'fullName', 'code', 'type'];

    public function portalMeasures()
    {
        return $this->hasMany(PortalMeasure::class);
    }

    public static function getForm()
    {

        return [
            'apiName' => 'measure',
            'title' => 'Создание Measure - обобщающая модель',
            'entityType' => 'entity',
            'groups' => [
                [
                    'groupName' => 'measure - обобщающая модель',
                    'entityType' => 'group',
                    'isCanAddField' => true,
                    'isCanDeleteField' => true,
                    'entityType' => 'group',
                    'fields' => [
                        [
                            'id' => 0,
                            'title' => 'name',
                            'entityType' => 'measure',
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
                            'title' => 'shortName ',
                            'entityType' => 'measure',
                            'name' => 'shortName',
                            'apiName' => 'shortName',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',
                            'isCanAddField' => false,
                            'isRequired' => true, //хотя бы одно поле в шаблоне должно быть

                        ],

                        [
                            'id' => 2,
                            'title' => 'fullName ',
                            'entityType' => 'measure',
                            'name' => 'fullName',
                            'apiName' => 'fullName',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',
                            'isCanAddField' => false,
                            'isRequired' => true, //хотя бы одно поле в шаблоне должно быть

                        ],

                        [
                            'id' => 3,
                            'title' => 'code',
                            'entityType' => 'measure',
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
                            'entityType' => 'measure',
                            'name' => 'type',
                            'apiName' => 'type',
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
