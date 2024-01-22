<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bitrixlist extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'type', 'group', 'name', 'title', 'bitrixId', 'portal_id'];

    public function portal()
    {
        return $this->belongsTo(Portal::class);
    }

    public static function getForm()
    {
        $allPortals = Portal::all();
        $portalsSelect = [];
        foreach($allPortals  as $portal){
            array_push($portalsSelect, [
                'id' => $portal->id,
                'domain' => $portal->domain,
                'name' => $portal->domain,
                'title' => $portal->domain,
            ]);
        };

        return [
            'apiName' => 'bitrixlist',
            'title' => 'Создание универсального списка',
            'entityType' => 'entity',
            'groups' => [
                [
                    'groupName' => 'Поля для Создания bitrixlist',
                    'entityType' => 'group',
                    'isCanAddField' => true,
                    'isCanDeleteField' => true,
                    'entityType' => 'group',
                    'fields' => [



                        [
                            'id' => 1,
                            'title' => 'Тип группы звоноков (sales, service)',
                            'name' => 'type',
                            'apiName' => 'type',
                            'type' =>  'string',
                            'entityType' => 'bitrixlist',
                            'validation' => 'required|max:255',
                            'initialValue' => '',
                            'isCanAddField' => false,
                            'isRequired' => true, //хотя бы одно поле в шаблоне должно быть
                        ],

                        [
                            'id' => 2,
                            'title' => 'К какой группе относится',
                            'name' => 'group',
                            'apiName' => 'group',
                            'type' =>  'string',
                            'entityType' => 'bitrixlist',
                            'validation' => 'required|max:255',
                            'initialValue' => '',
                            'isCanAddField' => false,
                            'isRequired' => true, //хотя бы одно поле в шаблоне должно быть
                        ],

                        [
                            'id' => 3,
                            'title' => 'name',
                            'entityType' => 'bitrixlist',
                            'name' => 'name',
                            'apiName' => 'name',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',
                            'isCanAddField' => false,
                            'isRequired' => true, //хотя бы одно поле в шаблоне должно быть

                        ],
                        [
                            'id' => 4,
                            'title' => 'Show Name (title)',
                            'entityType' => 'bitrixlist',
                            'name' => 'title',
                            'apiName' => 'title',
                            'type' =>  'string',
                            'validation' => 'required|max:255',
                            'initialValue' => '',
                            'isCanAddField' => false,
                            'isRequired' => true, //хотя бы одно поле в шаблоне должно быть

                        ],

                        [
                            'id' => 5,
                            'title' => 'ID в битриксе !',
                            'entityType' => 'bitrixlist',
                            'name' => 'bitrixId',
                            'apiName' => 'bitrixId',
                            'type' =>  'number',
                            'validation' => 'required|max:255',
                            'initialValue' => '',

                            'isCanAddField' => false,

                        ],
                        [
                            'id' => 6,
                            'title' => 'ID в битриксе !',
                            'entityType' => 'bitrixlist',
                            'name' => 'bitrixId',
                            'apiName' => 'bitrixId',
                            'type' =>  'select',
                            'validation' => 'required|max:255',
                            'initialValue' => '',
                            'items' => $portalsSelect,
                            'isCanAddField' => false,

                        ],



                    ],

                    'relations' => [],

                ]
            ]
        ];
    }
}
