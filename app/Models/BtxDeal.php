<?php

namespace App\Models;

use App\Http\Controllers\PortalController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BtxDeal extends Model
{
    use HasFactory;
    protected $with = [
        'categories',
        'bitrixfields'
    ];

    public function portal()
    {
        return $this->belongsTo(Portal::class);
    }



    public function categories()
    {
        return $this->morphMany(BtxCategory::class, 'entity');
    }

    public function bitrixfields()
    {
        return $this->morphMany(Bitrixfield::class, 'entity');
    }

    public function callingFields()
    {
        return $this->morphMany(Bitrixfield::class, 'entity')->where('parent_type', 'calling');
    }

    public function offerFields()
    {
        return $this->morphMany(Bitrixfield::class, 'entity')->where('parent_type', 'konstructor');
    }


    public static function getForm($portalId)
    {

        $portalsSelect = PortalController::getSelectPortals($portalId);

        return [
            'apiName' => 'deal',
            'title' => 'Создание Сделка - обобщающая модель',
            'entityType' => 'entity',
            'groups' => [
                [
                    'groupName' => 'Сделка - обобщающая модель',
                    'entityType' => 'group',
                    'isCanAddField' => true,
                    'isCanDeleteField' => true,
                    'entityType' => 'group',
                    'fields' => [
                        [
                            'id' => 0,
                            'title' => 'name',
                            'entityType' => 'deal',
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
                            'title' => 'Show Name (title)',
                            'entityType' => 'deal',
                            'name' => 'title',
                            'apiName' => 'title',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',
                            'isCanAddField' => false,
                            'isRequired' => true, //хотя бы одно поле в шаблоне должно быть

                        ],

                        [
                            'id' => 2,
                            'title' => 'code',
                            'entityType' => 'deal',
                            'name' => 'code',
                            'apiName' => 'code',
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





                    ],

                    'relations' => [],

                ]
            ]
        ];
    }



    // protected static function boot()
    // {
    //     parent::boot();

    //     static::deleting(function ($deal) {
    //         // Удаление связанных категорий
    //         $deal->categories()->delete();

    //         // Удаление связанных полей и их элементов
    //         $deal->bitrixfields->each(function ($field) {
    //             $field->items()->delete(); // Удаляем связанные items
    //             $field->delete();         // Удаляем поле
    //         });
    //     });
    // }
}
