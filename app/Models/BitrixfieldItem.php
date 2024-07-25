<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BitrixfieldItem extends Model
{
    use HasFactory;
    protected $fillable = [];

    public function bitrixField()
    {
        return $this->belongsTo(Bitrixfield::class);
    }

    public function portalContracts()
    {
        return $this->hasMany(PortalContract::class);
    }

    public static function getForm($bitrixFieldId)
    {
        $btxField = Bitrixfield::find($bitrixFieldId);

        return [
            'apiName' => 'bitrixfielditem',
            'title' => 'Items Полей битрикса типа список',
            'entityType' => 'entity',
            'groups' => [
                [
                    'groupName' => 'Items Параметры',
                    'entityType' => 'group',
                    'isCanAddField' => true,
                    'isCanDeleteField' => true,
                    'entityType' => 'group',
                    'fields' => [
                        [
                            'id' => 0,
                            'title' => 'имя элемента филда в битрикс',
                            'entityType' => 'bitrixfielditem',
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
                            'title' => 'Отображаемое имя элемента',
                            'entityType' => 'bitrixfielditem',
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
                            'title' => 'Код для ассоциаций должен совпадать с битрикс CODE элемента',
                            'entityType' => 'bitrixfielditem',
                            'name' => 'code',
                            'apiName' => 'code',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',

                            'isCanAddField' => false,

                        ],




                        [
                            'id' => 3,
                            'title' => 'id сущности родителя - филда  BtxField bitrixfield_id',
                            'entityType' => 'bitrixfielditem',
                            'name' => 'bitrixfield_id',
                            'apiName' => 'bitrixfield_id',
                            'type' =>  'select',
                            'validation' => 'required|max:255',
                            'initialValue' => $bitrixFieldId,
                            'value' => $bitrixFieldId,
                            'isCanAddField' => false,
                            'isRequired' => true, //хотя бы одно поле в шаблоне должно быть
                            'items' => [
                                $btxField
                            ]

                        ],




                        [
                            'id' => 4,
                            'title' => 'bitrixId',
                            'entityType' => 'bitrixfielditem',
                            'name' => 'bitrixId',
                            'apiName' => 'bitrixId',
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
