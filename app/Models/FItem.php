<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'code',
        'fieldNumber',
        'fieldId',
        'order',
        'value',
        'bitixId',

    ];

    public function field()
    {
        return $this->belongsTo(Field::class, 'fieldId', 'id');
    }

    public static function getForm()
    {

        return [
            'apiName' => 'fitem',
            'title' => 'Items for array-fields',
            'entityType' => 'entity',
            'groups' =>
            [[
                'groupName' => 'Создание Элементов списка',
                'entityType' => 'group',
                // 'type' => 'portal',
                'isCanAddField' => false,
                'isCanDeleteField' => false, //нельзя удалить ни одно из инициальзационных fields
                'isRequired' => true, //хотя бы одно поле в шаблоне должно быть
                'fields' => [
                    [
                        'title' => 'number',
                        'entityType' => 'fitem',
                        'name' => 'number',
                        'apiName' => 'number',
                        'type' =>  'number',
                        'validation' => 'required|max:255',
                        'initialValue' => '',

                        'isCanAddField' => false,

                    ],
                    [
                        'title' => 'code',
                        'entityType' => 'fitem',
                        'name' => 'code',
                        'apiName' => 'code',
                        'type' =>  'string',
                        'validation' => 'required|max:255',
                        'initialValue' => '',

                        'isCanAddField' => false,

                    ],
                    [
                        'title' => 'fieldNumber',
                        'entityType' => 'fitem',
                        'name' => 'fieldNumber',
                        'apiName' => 'fieldNumber',
                        'type' =>  'number',
                        'validation' => '',
                        'initialValue' => '',

                        'isCanAddField' => false,

                    ],

                    [
                        'title' => 'Порядок в списке',
                        'entityType' => 'fitem',
                        'name' => 'order',
                        'apiName' => 'order',
                        'type' =>  'number',
                        'validation' => 'required|max:255',
                        'initialValue' => '',

                        'isCanAddField' => false,

                    ],
                    [
                        'title' => 'Значение',
                        'entityType' => 'fitem',
                        'name' => 'value',
                        'apiName' => 'value',
                        'type' =>  'string',
                        'validation' => 'required|max:255',
                        'initialValue' => '',

                        'isCanAddField' => false,

                    ],
                    [
                        'title' => 'bitrixId',
                        'entityType' => 'fitem',
                        'name' => 'bitrixId',
                        'apiName' => 'bitrixId',
                        'type' =>  'string',
                        'validation' => 'required|max:255',
                        'initialValue' => '',

                        'isCanAddField' => false,

                    ],
                   
                ],
                'relations' => [],
            ]]
        ];
    }
}
