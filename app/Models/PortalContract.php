<?php

namespace App\Models;

use App\Http\Controllers\PortalController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortalContract extends Model
{
    use HasFactory;

    protected $with = ['contract', 'portalMeasure'];


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


    // Accessor для title
    public function getTitleAttribute($value)
    {
        // Возвращаем пользовательский title, если он есть
        return $this->attributes['title'] ?? $this->contract->title;
    }

    // Accessor для template
    public function getTemplateAttribute($value)
    {
        // Возвращаем пользовательский template, если он есть
        return $this->attributes['template'] ?? $this->contract->template;
    }

    // Accessor для order
    public function getOrderAttribute($value)
    {
        // Возвращаем пользовательский order, если он есть
        return $this->attributes['order'] ?? $this->contract->order;
    }


    public static function getForm($portalId)
    {

        $portalsSelect = PortalController::getSelectPortals($portalId);
        $portal = Portal::find($portalId);
        $contracts = Contract::all();
        $deal = $portal->deals()->first();
        $fields = $deal->bitrixfields;
        $selectFieldItems = [];

        foreach ($fields as  $field) {
          if( $field['code'] == 'contract_type'){

            if(!empty($field->items)){
                foreach ($field->items  as $item) {
                    array_push($selectFieldItems, [
                        'id' => $item->id,
                        // 'domain' => $portal->domain,
                        'name' => $item->name,
                        'code' => $portal->code,
                    ]);
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
                            'initialValue' => $contracts[0]['id'],
                            'items' => $contracts,
                            'isCanAddField' => false,

                        ],




                    ],

                    'relations' => [],

                ]
            ]
        ];
    }
}
