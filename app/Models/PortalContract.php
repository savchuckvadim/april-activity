<?php

namespace App\Models;

use App\Http\Controllers\PortalController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PDO;

class PortalContract extends Model
{
    use HasFactory;

    protected $with = ['contract', 'portalMeasure', 'dealContractItem'];
    protected $fillable = ['portal_id', 'contract_id', 'portal_measure_id', 'title', 'template', 'order', 'bitrixfield_item_id'];


    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function portal()
    {
        return $this->belongsTo(Portal::class);
    }

    public function portalMeasure()
    {
        return $this->belongsTo(PortalMeasure::class);
    }
    
    public function dealContractItem()
    {
        return $this->belongsTo(BitrixfieldItem::class);
    }

    // // Accessor для title
    // public function getTitleAttribute($value)
    // {
    //     // Возвращаем пользовательский title, если он есть
    //     return $this->attributes['title'] ?? $this->contract->title;
    // }

    // // Accessor для template
    // public function getTemplateAttribute($value)
    // {
    //     // Возвращаем пользовательский template, если он есть
    //     return $this->attributes['template'] ?? $this->contract->template;
    // }

    // // Accessor для order
    // public function getOrderAttribute($value)
    // {
    //     // Возвращаем пользовательский order, если он есть
    //     return $this->attributes['order'] ?? $this->contract->order;
    // }


    public static function getForm($portalId)
    {

        $portalsSelect = PortalController::getSelectPortals($portalId);
        $portal = Portal::find($portalId);
        $portalMesures = $portal->measures;
        $selectportalMesures = PortalContract::getSelectItems($portalMesures);
        $currentportalMesuresId = PortalContract::getSelectItemId($portalMesures);

        $contracts = Contract::all();
        $selectContracts = PortalContract::getSelectItems($contracts);
        $currentContractId = PortalContract::getSelectItemId($contracts);
        $deal = $portal->deals()->first();
        $fields = $deal->bitrixfields;
        $selectFieldItems = [];
        $items = [];
        foreach ($fields as  $field) {
            if ($field['code'] == 'contract_type') {

                if (!empty($field->items)) {

                    foreach ($field->items  as $item) {
                        array_push($selectFieldItems, $item);
                        // array_push($selectFieldItems, [
                        //     'id' => $item->id,
                        //     // 'domain' => $portal->domain,
                        //     'name' => $item->title,
                        //     'code' => $item->code,
                        // ]);
                    };
                }
            }
        }


        return [
            'apiName' => 'portal_contract',
            'title' => 'Создание portal_contract - обобщающая модель',
            'entityType' => 'entity',
            'groups' => [
                [
                    'groupName' => 'portal_contract - обобщающая модель',
                    'entityType' => 'group',
                    'isCanAddField' => true,
                    'isCanDeleteField' => true,
                    'entityType' => 'group',
                    'fields' => [
                        [
                            'id' => 0,
                            'title' => 'title',
                            'entityType' => 'portal_contract',
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
                            'title' => 'template link nullable',
                            'entityType' => 'portal_contract',
                            'name' => 'template',
                            'apiName' => 'template',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',
                            'isCanAddField' => false,
                            'isRequired' => true, //хотя бы одно поле в шаблоне должно быть

                        ],

                        [
                            'id' => 2,
                            'title' => 'order',
                            'entityType' => 'portal_contract',
                            'name' => 'order',
                            'apiName' => 'order',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',

                            'isCanAddField' => false,

                        ],
                        [
                            'id' => 3,
                            'title' => 'Relation portal_id',
                            'entityType' => 'portal_contract',
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
                            'title' => 'Relation bitrixfield_item_id',
                            'entityType' => 'portal_contract',
                            'name' => 'bitrixfield_item_id',
                            'apiName' => 'bitrixfield_item_id',
                            'type' =>  'select',
                            'validation' => 'required',
                            'initialValue' => $selectFieldItems[0]['id'],
                            'items' => $selectFieldItems,
                            'isCanAddField' => false,

                        ],

                        [
                            'id' => 5,
                            'title' => 'Relation contract_id',
                            'entityType' => 'portal_contract',
                            'name' => 'contract_id',
                            'apiName' => 'contract_id',
                            'type' =>  'select',
                            'validation' => 'required',
                            'initialValue' => $currentContractId,
                            'items' => $selectContracts,
                            'isCanAddField' => false,

                        ],

                        [
                            'id' => 6,
                            'title' => 'Relation portal_measure_id',
                            'entityType' => 'portal_contract',
                            'name' => 'portal_measure_id',
                            'apiName' => 'portal_measure_id',
                            'type' =>  'select',
                            'validation' => 'required',
                            'initialValue' => $currentportalMesuresId,
                            'items' => $selectportalMesures,
                            'isCanAddField' => false,

                        ],
                        [
                            'id' => 7,
                            'title' => 'productName nullable',
                            'entityType' => 'portal_contract',
                            'name' => 'productName',
                            'apiName' => 'productName',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',
                            'isCanAddField' => false,
                            'isRequired' => true, //хотя бы одно поле в шаблоне должно быть

                        ],

                        [
                            'id' => 7,
                            'title' => 'description nullable',
                            'entityType' => 'portal_contract',
                            'name' => 'description',
                            'apiName' => 'description',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',
                            'isCanAddField' => false,
                            'isRequired' => true, //хотя бы одно поле в шаблоне должно быть

                        ],



                    ],

                    'relations' => $deal,
                    'items' => $items,

                ]
            ]
        ];
    }

    public static function getSelectItems($items)
    {
        $selectItems = [];

        foreach ($items  as $item) {
            array_push($selectItems, [
                'id' => $item->id,

                'name' => $item->name,
                'title' => $item->name,
            ]);
        };
        return $selectItems;
    }
    public static function getSelectItemId($items)
    {
        $result = 0;

        foreach ($items  as $key => $item) {
            if (!$key) {
                $result = $item['id'];
            }
        };

        return $result;
    }
}
