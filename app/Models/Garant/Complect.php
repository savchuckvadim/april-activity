<?php

namespace App\Models\Garant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Complect extends Model
{
    use HasFactory;

    protected $fillable = [
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
        'withConsalting',
        'withServices',
        'withLt',
        'isChanging',
        'withDefault',
    ];

    protected $casts = [
        'withABS' => 'boolean',
        'withConsalting' => 'boolean',
        'withServices' => 'boolean',
        'withLt' => 'boolean',
        'isChanging' => 'boolean',
        'withDefault' => 'boolean',
    ];

    /**
     * Связь многие ко многим с инфоблоками
     */
    public function infoblocks()
    {
        return $this->belongsToMany(Infoblock::class, 'complect_infoblock');
    }

    public static function getForm()
    {
        $infoblocks = Infoblock::all();
        $iblockFields = [];
        foreach ($infoblocks as $key => $infoblocks) {
            array_push(
                $iblockFields,
                [
                    'id' => 13 + $key,
                    'title' => $infoblocks->name,
                    'entityType' => 'complects',
                    'name' => $infoblocks->code,
                    'apiName' => $infoblocks->id,
                    'type' =>  'boolean',
                    'validation' => 'required|max:255',
                    'initialValue' => '',
                    'isCanAddField' => false,
                    'isRequired' => true, //хотя бы одно поле в шаблоне должно быть

                ]
            );
        }
        $fields = [
            [
                'id' => 0,
                'title' => 'name',
                'entityType' => 'complects',
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
                'title' => 'fullName',
                'entityType' => 'complects',
                'name' => 'fullName',
                'apiName' => 'fullName',
                'type' =>  'string',
                'validation' => 'required|max:255',
                'initialValue' => '',
                'isCanAddField' => false,
                'isRequired' => true, //хотя бы одно поле в шаблоне должно быть

            ],
            [
                'id' => 2,
                'title' => 'shortName',
                'entityType' => 'complects',
                'name' => 'shortName',
                'apiName' => 'shortName',
                'type' =>  'string',
                'validation' => 'required|max:255',
                'initialValue' => '',
                'isCanAddField' => false,
                'isRequired' => true, //хотя бы одно поле в шаблоне должно быть

            ],
            [
                'id' => 3,
                'title' => 'description',
                'entityType' => 'complects',
                'name' => 'description',
                'apiName' => 'description',
                'type' =>  'text',
                'validation' => 'required|max:255',
                'initialValue' => '',
                'isCanAddField' => false,
                'isRequired' => true, //хотя бы одно поле в шаблоне должно быть

            ],
            [
                'id' => 4,
                'title' => 'code',
                'entityType' => 'complects',
                'name' => 'code',
                'apiName' => 'code',
                'type' =>  'string',
                'validation' => 'required|max:255',
                'initialValue' => '',
                'isCanAddField' => false,
                'isRequired' => true, //хотя бы одно поле в шаблоне должно быть

            ],
            [
                'id' => 5,
                'title' => 'type',
                'entityType' => 'complects',
                'name' => 'type',
                'apiName' => 'type',
                'type' =>  'string',
                'validation' => 'required|max:255',
                'initialValue' => '',
                'isCanAddField' => false,
                'isRequired' => true, //хотя бы одно поле в шаблоне должно быть

            ],

            [
                'id' => 6,
                'title' => 'color',
                'entityType' => 'complects',
                'name' => 'color',
                'apiName' => 'color',
                'type' =>  'string',
                'validation' => 'required|max:255',
                'initialValue' => '',
                'isCanAddField' => false,
                'isRequired' => true, //хотя бы одно поле в шаблоне должно быть

            ],
            [
                'id' => 7,
                'title' => 'productType',
                'entityType' => 'complects',
                'name' => 'productType',
                'apiName' => 'productType',
                'type' =>  'string',
                'validation' => 'required|max:255',
                'initialValue' => '',
                'isCanAddField' => false,
                'isRequired' => true, //хотя бы одно поле в шаблоне должно быть

            ],
            [
                'id' => 8,
                'title' => 'weight',
                'entityType' => 'complects',
                'name' => 'weight',
                'apiName' => 'weight',
                'type' =>  'number',
                'validation' => 'required|max:255',
                'initialValue' => '',
                'isCanAddField' => false,
                'isRequired' => true, //хотя бы одно поле в шаблоне должно быть

            ],
            [
                'id' => 9,
                'title' => 'abs',
                'entityType' => 'complects',
                'name' => 'abs',
                'apiName' => 'abs',
                'type' =>  'number',
                'validation' => 'required|max:255',
                'initialValue' => '',
                'isCanAddField' => false,
                'isRequired' => true, //хотя бы одно поле в шаблоне должно быть

            ],
            [
                'id' => 10,
                'title' => 'withABS',
                'entityType' => 'complects',
                'name' => 'withABS',
                'apiName' => 'withABS',
                'type' =>  'boolean',
                'validation' => 'required|max:255',
                'initialValue' => '',
                'isCanAddField' => false,
                'isRequired' => true, //хотя бы одно поле в шаблоне должно быть

            ],
            [
                'id' => 11,
                'title' => 'withServices',
                'entityType' => 'complects',
                'name' => 'withServices',
                'apiName' => 'withServices',
                'type' =>  'boolean',
                'validation' => 'required|max:255',
                'initialValue' => '',
                'isCanAddField' => false,
                'isRequired' => true, //хотя бы одно поле в шаблоне должно быть

            ],
            [
                'id' => 12,
                'title' => 'withLt',
                'entityType' => 'complects',
                'name' => 'withLt',
                'apiName' => 'withLt',
                'type' =>  'boolean',
                'validation' => 'required|max:255',
                'initialValue' => '',
                'isCanAddField' => false,
                'isRequired' => true, //хотя бы одно поле в шаблоне должно быть

            ],
            [
                'id' => 13,
                'title' => 'isChanging',
                'entityType' => 'complects',
                'name' => 'isChanging',
                'apiName' => 'isChanging',
                'type' =>  'boolean',
                'validation' => 'required|max:255',
                'initialValue' => '',
                'isCanAddField' => false,
                'isRequired' => true, //хотя бы одно поле в шаблоне должно быть

            ],
            [
                'id' => 13,
                'title' => 'withDefault',
                'entityType' => 'complects',
                'name' => 'withDefault',
                'apiName' => 'withDefault',
                'type' =>  'boolean',
                'validation' => 'required|max:255',
                'initialValue' => '',
                'isCanAddField' => false,
                'isRequired' => true, //хотя бы одно поле в шаблоне должно быть

            ],


        ];
        $fields = array_merge($fields, $iblockFields);

        return [
            'apiName' => 'complects',
            'title' => 'Комплект Гарант',
            'entityType' => 'entity',
            'groups' => [
                [
                    'groupName' => 'Комплект Гарант',
                    'entityType' => 'group',
                    'isCanAddField' => true,
                    'isCanDeleteField' => true,
                    'fields' =>  $fields,

                    'relations' => [],

                ]
            ]
        ];
    }
}
