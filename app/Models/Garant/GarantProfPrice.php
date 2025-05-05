<?php

namespace App\Models\Garant;

use App\Models\Garant\Complect;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GarantProfPrice extends Model
{
    use HasFactory;
    protected $fillable = [
        'complect_id',
        'garant_package_id',
        'supply_id',
        'supply_id',
        'region_type',
        'supply_type',
        'value',
        'discount',

    ];

    public function complect()
    {
        return $this->belongsTo(Complect::class, 'complect_id');
    }

    public function garantPackage()
    {
        return $this->belongsTo(GarantPackage::class, 'garant_package_id');
    }

    public function supply()
    {
        return $this->belongsTo(Supply::class, 'supply_id');
    }

    public static function getForm()
    {
        $complects = Complect::all();
        $garantPackages = GarantPackage::all();
        $supplies = Supply::all();

        $complectsItems = [[
            'id' => '',
            'title' => 'Не выбран',
            'entityType' => 'garant_prof_price',
            'name' => '',
            'apiName' => '',
            'type' => '',
            'validation' => '',
            'initialValue' => '',
            'isCanAddField' => false,
            'isRequired' => true,
        ]];
        foreach ($complects as $key => $complect) {
            array_push(
                $complectsItems,
                [
                    'id' => $complect->id,
                    'title' => $complect->name,
                    'entityType' => 'garant_prof_price',
                    'name' => $complect->code,
                    'apiName' => $complect->id,
                    'type' => 'select',
                    'validation' => 'required|max:255',
                    'initialValue' => '',
                    'isCanAddField' => false,
                    'isRequired' => true,
                ]
            );
        }

        $garantPackagesItems = [[
            'id' => '',
            'title' => 'Не выбран',
            'entityType' => 'garant_prof_price',
            'name' => '',
            'apiName' => '',
            'type' => '',
            'validation' => '',
            'initialValue' => '',
            'isCanAddField' => false,
            'isRequired' => true,
        ]];
        foreach ($garantPackages as $key => $garantPackage) {
            array_push(
                $garantPackagesItems,
                [
                    'id' => $garantPackage->id,
                    'title' => $garantPackage->name,
                    'entityType' => 'garant_prof_price',
                    'name' => $garantPackage->code,
                    'apiName' => $garantPackage->id,
                    'type' => 'select',
                    'validation' => 'required|max:255',
                    'initialValue' => '',
                    'isCanAddField' => false,
                    'isRequired' => true,
                ]
            );
        }

        $suppliesItems = [[
            'id' => '',
            'title' => 'Не выбран',
            'entityType' => 'garant_prof_price',
            'name' => '',
            'apiName' => '',
            'type' => '',
            'validation' => '',
            'initialValue' => '',
            'isCanAddField' => false,
            'isRequired' => true,
        ]];
        foreach ($supplies as $key => $supply) {
            array_push(
                $suppliesItems,
                [
                    'id' => $supply->id,
                    'title' => $supply->name,
                    'entityType' => 'garant_prof_price',
                    'name' => $supply->code,
                    'apiName' => $supply->id,
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
                'title' => 'complect_id',
                'entityType' => 'garant_prof_price',
                'name' => 'complect_id',
                'apiName' => 'complect_id',
                'type' => 'select',
                'validation' => 'required|max:255',
                'initialValue' => '',
                'isCanAddField' => false,
                'isRequired' => true,
                'items' => $complectsItems,
            ],
            [
                'id' => 1,
                'title' => 'garant_package_id',
                'entityType' => 'garant_prof_price',
                'name' => 'garant_package_id',
                'apiName' => 'garant_package_id',
                'type' => 'select',
                'validation' => 'required|max:255',
                'initialValue' => '',
                'isCanAddField' => false,
                'isRequired' => true,
                'items' => $garantPackagesItems,
            ],
            [
                'id' => 2,
                'title' => 'supply_id',
                'entityType' => 'garant_prof_price',
                'name' => 'supply_id',
                'apiName' => 'supply_id',
                'type' => 'select',
                'validation' => 'required|max:255',
                'initialValue' => '',
                'isCanAddField' => false,
                'isRequired' => true,
                'items' => $suppliesItems,
            ],
            [
                'id' => 3,
                'title' => 'region_type',
                'entityType' => 'garant_prof_price',
                'name' => 'region_type',
                'apiName' => 'region_type',
                'type' => 'select',
                'validation' => 'required|max:255',
                'initialValue' => '',
                'isCanAddField' => false,
                'isRequired' => true,
                'items' => [
                    [
                        'id' => 'msk',
                        'title' => 'Москва',
                        'value' => 'msk',
                    ],
                    [
                        'id' => 'rgn',
                        'title' => 'Регионы',
                        'value' => 'rgn',
                    ],

                ]
            ],
            [
                'id' => 4,
                'title' => 'supply_type',
                'entityType' => 'garant_prof_price',
                'name' => 'supply_type',
                'apiName' => 'supply_type',
                'type' => 'select',
                'validation' => 'required|max:255',
                'initialValue' => '',
                'isCanAddField' => false,
                'isRequired' => true,
            ],
            [
                'id' => 5,
                'title' => 'value',
                'entityType' => 'garant_prof_price',
                'name' => 'value',
                'apiName' => 'value',
                'type' => 'number',
                'validation' => 'required|max:255',
                'initialValue' => '',
                'isCanAddField' => false,
                'isRequired' => true,
            ],
            [
                'id' => 6,
                'title' => 'discount',
                'entityType' => 'garant_prof_price',
                'name' => 'discount',
                'apiName' => 'discount',
                'type' => 'number',
                'validation' => '',
                'initialValue' => '',
                'isCanAddField' => false,
                'isRequired' => true,
            ],
        ];
        return [
            'apiName' => 'garant_prof_price',
            'title' => 'Цены Гарант',
            'entityType' => 'entity',
            'groups' => [
                [
                    'groupName' => 'Цены на профкомплекты и пакеты без АБС',
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
