<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BtxStage extends Model
{
    use HasFactory;
    protected $table = 'btx_stages';

    protected $fillable = [
        'title', 
        'name', 
        'code',
        'color',
        'bitrixId',
        'isActive',
        'btx_category_id',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(BtxCategory::class);
    }


    public static function getForm($categoryId)
    {
        $btxParentCategory = BtxCategory::find($categoryId);



        return [
            'apiName' => 'btx_stage',
            'title' => 'Стадии',
            'entityType' => 'entity',
            'groups' => [
                [
                    'groupName' => 'Стадии',
                    'entityType' => 'group',
                    'isCanAddField' => true,
                    'isCanDeleteField' => true,
                    'entityType' => 'group',
                    'fields' => [

                        [
                            'id' => 0,
                            'title' => 'Отображаемое Стадии',
                            'entityType' => 'btx_stage',
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
                            'title' => 'имя Стадии в битрикс',
                            'entityType' => 'btx_stage',
                            'name' => 'name',
                            'apiName' => 'name',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',
                            'isCanAddField' => false,
                            'isRequired' => true, //хотя бы одно поле в шаблоне должно быть

                        ],




                        [
                            'id' => 2,
                            'title' => 'CODE кодовое название стадии',
                            'entityType' => 'btx_stage',
                            'name' => 'code',
                            'apiName' => 'code',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',

                            'isCanAddField' => false,

                        ],




                        [
                            'id' => 3,
                            'title' => 'bitrixId',
                            'entityType' => 'btx_stage',
                            'name' => 'bitrixId',
                            'apiName' => 'bitrixId',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',

                            'isCanAddField' => false,

                        ],
                        [
                            'id' => 4,
                            'title' => 'color',
                            'entityType' => 'btx_stage',
                            'name' => 'color',
                            'apiName' => 'color',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',

                            'isCanAddField' => false,

                        ],

                        [
                            'id' => 5,
                            'title' => 'isActive',
                            'entityType' => 'btx_stage',
                            'name' => 'isActive',
                            'apiName' => 'isActive',
                            'type' =>  'boolean',
                            'validation' => 'required|max:255',
                            'initialValue' => 'true',
                            'value' => 'true',

                            'isCanAddField' => false,

                        ],

                        [
                            'id' => 6,
                            'title' => 'btx_category_id',
                            'entityType' => 'btx_stage',
                            'name' => 'btx_category_id',
                            'apiName' => 'btx_category_id',
                            'type' =>  'select',
                            'validation' => 'required|max:255',
                            'initialValue' => $categoryId,
                            'value' => '',
                            'items' => [
                                $btxParentCategory
                            ],

                            'isCanAddField' => false,

                        ],

                    ],

                    'relations' => [],

                ]
            ]
        ];
    }
}
