<?php

namespace App\Models;

use App\Http\Controllers\PortalController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortalMeasure extends Model
{
    use HasFactory;
    protected $with = ['measure'];
    protected $table = 'portal_measure'; // Указание, какая таблица используется для этой модели


    public function measure()
    {
        return $this->belongsTo(Measure::class);
    }

    public function portal()
    {
        return $this->belongsTo(Portal::class);
    }

    public function portalContracts()
    {
        return $this->hasMany(PortalContract::class);
    }


  

    public static function getForm($portalId)
    {

        $portalsSelect = PortalController::getSelectPortals($portalId);
        $measuresSelect = Measure::all();
        $currentMeasureSelect = [];
        $currentMeasure = Measure::first();
        $currentMeasureSelectId = 0;

        if (!empty($currentMeasure)) {
            if (!empty($currentMeasure['id'])) {
                $currentMeasureSelectId = $currentMeasure['id'];
            }
        }


        foreach ($measuresSelect  as $measure) {
            array_push($currentMeasureSelect, [
                'id' => $measure->id,
                'name' => $measure->name,
                'title' => $measure->name,
            ]);
        };

        return [
            'apiName' => 'Portal Measure',
            'title' => 'Portal Measure - обобщающая модель',
            'entityType' => 'entity',
            'groups' => [
                [
                    'groupName' => 'Portal Measure',
                    'entityType' => 'group',
                    'isCanAddField' => true,
                    'isCanDeleteField' => true,
                    'entityType' => 'group',
                    'fields' => [
                        [
                            'id' => 0,
                            'title' => 'name',
                            'entityType' => 'portal_measure',
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
                            'title' => 'shortName',
                            'entityType' => 'portal_measure',
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
                            'title' => 'fullName',
                            'entityType' => 'portal_measure',
                            'name' => 'fullName',
                            'apiName' => 'fullName',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',

                            'isCanAddField' => false,

                        ],
                        [
                            'id' => 2,
                            'title' => 'bitrixId',
                            'entityType' => 'portal_measure',
                            'name' => 'bitrixId',
                            'apiName' => 'bitrixId',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',

                            'isCanAddField' => false,

                        ],
                        [
                            'id' => 3,
                            'title' => 'Relation portal_id',
                            'entityType' => 'deal',
                            'name' => 'portal_id',
                            'apiName' => 'portal_id',
                            'type' =>  'select',
                            'validation' => 'required',
                            'initialValue' => $portalId,
                            'items' => $portalsSelect,
                            'isCanAddField' => false,

                        ],
                        [
                            'id' => 4,
                            'title' => 'Relation measure_id',
                            'entityType' => 'deal',
                            'name' => 'measure_id',
                            'apiName' => 'measure_id',
                            'type' =>  'select',
                            'validation' => 'required',
                            'initialValue' => $currentMeasureSelectId,
                            'items' => $currentMeasureSelect,
                            'isCanAddField' => false,

                        ],





                    ],

                    'relations' => [],

                ]
            ]
        ];
    }
}
