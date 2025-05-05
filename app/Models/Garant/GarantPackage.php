<?php

namespace App\Models\Garant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GarantPackage extends Model
{
    use HasFactory;
    protected $fillable = [
        'infoblock_id',
        'info_group_id',
        'name',
        'fullName',
        'shortName',
        'description',
        'code',
        'type',
        'color',
        'weight',
        'abs',
        'number',
        'productType',
        'withABS',
        'isChanging',
        'withDefault'
    ];

    protected $casts = [
        'withABS' => 'boolean',
        'isChanging' => 'boolean',
        'withDefault' => 'boolean',
    ];

    /**
     * Связь с инфоблоком
     */
    public function infoblock()
    {
        return $this->belongsTo(Infoblock::class, 'infoblock_id');
    }

    /**
     * Связь с группой инфоблоков
     */
    public function infoGroup()
    {
        return $this->belongsTo(InfoGroup::class, 'info_group_id');
    }

    public static function getForm()
    {
        $infoblocks = Infoblock::all();
        $infoGroups = InfoGroup::all();
        $iblockFields = [[
            'id' => '',
            'title' => 'Не выбран',
            'entityType' => 'garant_packages',
            'name' => '',
            'apiName' => '',
            'type' => '',
            'validation' => '',
            'initialValue' => '',
            'isCanAddField' => false,
            'isRequired' => true,
        ]];
        foreach ($infoblocks as $key => $infoblock) {
            array_push(
                $iblockFields,
                [
                    'id' => $infoblock->id,
                    'title' => $infoblock->name,
                    'entityType' => 'garant_packages',
                    'name' => $infoblock->code,
                    'apiName' => $infoblock->id,
                    'type' => 'select',
                    'validation' => 'required|max:255',
                    'initialValue' => '',
                    'isCanAddField' => false,
                    'isRequired' => true,
                ]
            );
        }

        $infoGroupFields = [[
            'id' => '',
            'title' => 'Не выбран',
            'entityType' => 'garant_packages',
            'name' => '',
            'apiName' => '',
            'type' => '',
            'validation' => '',
            'initialValue' => '',
            'isCanAddField' => false,
            'isRequired' => true,
        ]];
        foreach ($infoGroups as $key => $infoGroup) {
            array_push(
                $infoGroupFields,
                [
                    'id' => $infoGroup->id,
                    'title' => $infoGroup->name,
                    'entityType' => 'garant_packages',
                    'name' => $infoGroup->code,
                    'apiName' => $infoGroup->id,
                    'type' => 'select',
                    'validation' => 'required|max:255',
                    'initialValue' => '',
                    'isCanAddField' => false,
                    'isRequired' => true,
                ]
            );
        }

        $fields = [
            [
                'id' => 0,
                'title' => 'name',
                'entityType' => 'garant_packages',
                'name' => 'name',
                'apiName' => 'name',
                'type' => 'string',
                'validation' => 'required|max:255',
                'initialValue' => '',
                'isCanAddField' => false,
                'isRequired' => true,
            ],
            [
                'id' => 1,
                'title' => 'fullName',
                'entityType' => 'garant_packages',
                'name' => 'fullName',
                'apiName' => 'fullName',
                'type' => 'string',
                'validation' => 'required|max:255',
                'initialValue' => '',
                'isCanAddField' => false,
                'isRequired' => true,
            ],
            [
                'id' => 2,
                'title' => 'shortName',
                'entityType' => 'garant_packages',
                'name' => 'shortName',
                'apiName' => 'shortName',
                'type' => 'string',
                'validation' => 'required|max:255',
                'initialValue' => '',
                'isCanAddField' => false,
                'isRequired' => true,
            ],
            [
                'id' => 3,
                'title' => 'description',
                'entityType' => 'garant_packages',
                'name' => 'description',
                'apiName' => 'description',
                'type' => 'text',
                'validation' => 'required|max:255',
                'initialValue' => '',
                'isCanAddField' => false,
                'isRequired' => true,
            ],
            [
                'id' => 4,
                'title' => 'code',
                'entityType' => 'garant_packages',
                'name' => 'code',
                'apiName' => 'code',
                'type' => 'string',
                'validation' => 'required|max:255',
                'initialValue' => '',
                'isCanAddField' => false,
                'isRequired' => true,
            ],
            [
                'id' => 5,
                'title' => 'type',
                'entityType' => 'garant_packages',
                'name' => 'type',
                'apiName' => 'type',
                'type' => 'string',
                'validation' => 'required|max:255',
                'initialValue' => '',
                'isCanAddField' => false,
                'isRequired' => true,
            ],
            [
                'id' => 6,
                'title' => 'color',
                'entityType' => 'garant_packages',
                'name' => 'color',
                'apiName' => 'color',
                'type' => 'string',
                'validation' => 'required|max:255',
                'initialValue' => '',
                'isCanAddField' => false,
                'isRequired' => true,
            ],
            [
                'id' => 7,
                'title' => 'productType',
                'entityType' => 'garant_packages',
                'name' => 'productType',
                'apiName' => 'productType',
                'type' => 'string',
                'validation' => 'required|max:255',
                'initialValue' => '',
                'isCanAddField' => false,
                'isRequired' => true,
            ],
            [
                'id' => 8,
                'title' => 'weight',
                'entityType' => 'garant_packages',
                'name' => 'weight',
                'apiName' => 'weight',
                'type' => 'number',
                'validation' => 'required|max:255',
                'initialValue' => '',
                'isCanAddField' => false,
                'isRequired' => true,
            ],
            [
                'id' => 9,
                'title' => 'abs',
                'entityType' => 'garant_packages',
                'name' => 'abs',
                'apiName' => 'abs',
                'type' => 'number',
                'validation' => 'required|max:255',
                'initialValue' => '',
                'isCanAddField' => false,
                'isRequired' => true,
            ],
            [
                'id' => 10,
                'title' => 'withABS',
                'entityType' => 'garant_packages',
                'name' => 'withABS',
                'apiName' => 'withABS',
                'type' => 'boolean',
                'validation' => 'required|max:255',
                'initialValue' => '',
                'isCanAddField' => false,
                'isRequired' => true,
            ],
            [
                'id' => 11,
                'title' => 'isChanging',
                'entityType' => 'garant_packages',
                'name' => 'isChanging',
                'apiName' => 'isChanging',
                'type' => 'boolean',
                'validation' => 'required|max:255',
                'initialValue' => '',
                'isCanAddField' => false,
                'isRequired' => true,
            ],
            [
                'id' => 12,
                'title' => 'withDefault',
                'entityType' => 'garant_packages',
                'name' => 'withDefault',
                'apiName' => 'withDefault',
                'type' => 'boolean',
                'validation' => 'required|max:255',
                'initialValue' => '',
                'isCanAddField' => false,
                'isRequired' => true,
            ],
            [
                'id' => 13,
                'title' => 'number',
                'entityType' => 'garant_packages',
                'name' => 'number',
                'apiName' => 'number',
                'type' => 'number',
                'validation' => 'required|max:255',
                'initialValue' => '',
                'isCanAddField' => false,
                'isRequired' => true,
            ],
            [
                'id' => 14,
                'title' => 'Relation infoblock_id',
                'entityType' => 'garant_packages',
                'name' => 'infoblock_id',
                'apiName' => 'infoblock_id',
                'type' =>  'select',
                'validation' => '',
                'initialValue' => '',
                'items' => $iblockFields,
                'isCanAddField' => false,

            ],
            [
                'id' => 15,
                'title' => 'Relation infoGroup_id',
                'entityType' => 'garant_packages',
                'name' => 'info_group_id',
                'apiName' => 'info_group_id',
                'type' =>  'select',
                'validation' => '',
                'initialValue' => '',
                'items' => $infoGroupFields,
                'isCanAddField' => false,

            ],
        ];



        // $fields = array_merge($fields, $iblockFields, $infoGroupFields);

        return [
            'apiName' => 'garant_packages',
            'title' => 'Пакет Гарант',
            'entityType' => 'entity',
            'groups' => [
                [
                    'groupName' => 'Пакет Гарант',
                    'entityType' => 'group',
                    'isCanAddField' => true,
                    'isCanDeleteField' => true,
                    'fields' => $fields,
                    'relations' => [
                        [
                            'name' => 'infoblock',
                            'type' => 'belongsTo',
                            'model' => 'Infoblock',
                            'nullable' => true
                        ],
                        [
                            'name' => 'infoGroup',
                            'type' => 'belongsTo',
                            'model' => 'InfoGroup',
                            'nullable' => true
                        ]
                    ],
                ]
            ]
        ];
    }
}
