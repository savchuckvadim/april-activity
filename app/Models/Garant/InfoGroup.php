<?php

namespace App\Models\Garant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfoGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'code',
        'name',
        'title',
        'description',
        'descriptionForSale',
        'shortDescription',
        'type',
        'productType',

    ];

    public function infoblocks()
    {
        return $this->hasMany(Infoblock::class, 'group_id');
    }
    
    public static function getForm()
    {

        return [
            'apiName' => 'infogroup',
            'title' => 'Группа инфоблоков',
            'entityType' => 'entity',
            'groups' =>
            [[
                'groupName' => 'Создание портала',
                'entityType' => 'group',
                // 'type' => 'portal',
                'isCanAddField' => false,
                'isCanDeleteField' => false, //нельзя удалить ни одно из инициальзационных fields
                'isRequired' => true, //хотя бы одно поле в шаблоне должно быть
                'fields' => [
                    [
                        'title' => 'Название',
                        'entityType' => 'infogroup',
                        'name' => 'name',
                        'apiName' => 'name',
                        'type' =>  'string',
                        'validation' => 'required|max:255',
                        'initialValue' => '',

                        'isCanAddField' => false,

                    ],
                    [
                        'title' => 'title',
                        'entityType' => 'infogroup',
                        'name' => 'title',
                        'apiName' => 'title',
                        'type' =>  'string',
                        'validation' => 'required|max:255',
                        'initialValue' => '',

                        'isCanAddField' => false,

                    ],
                    [
                        'title' => 'number',
                        'entityType' => 'infogroup',
                        'name' => 'name',
                        'apiName' => 'number',
                        'type' =>  'number',
                        'validation' => 'required|max:255',
                        'initialValue' => '',

                        'isCanAddField' => false,

                    ],
                    [
                        'title' => 'Код для ассоциаций',
                        'entityType' => 'infogroup',
                        'name' => 'code',
                        'apiName' => 'code',
                        'type' =>  'string',
                        'validation' => 'required|max:255',
                        'initialValue' => '',

                        'isCanAddField' => false,

                    ],
              
                    
                    [
                        'title' => 'description',
                        'entityType' => 'infogroup',
                        'name' => 'description',
                        'apiName' => 'description',
                        'type' =>  'text',
                        'validation' => 'required|max:255',
                        'initialValue' => '',

                        'isCanAddField' => false,

                    ],
                    [
                        'title' => 'descriptionForSale',
                        'entityType' => 'infogroup',
                        'name' => 'descriptionForSale',
                        'apiName' => 'descriptionForSale',
                        'type' =>  'text',
                        'validation' => 'required|max:255',
                        'initialValue' => '',

                        'isCanAddField' => false,

                    ],
                    [
                        'title' => 'shortDescription',
                        'entityType' => 'infogroup',
                        'name' => 'shortDescription',
                        'apiName' => 'shortDescription',
                        'type' =>  'text',
                        'validation' => 'required|max:255',
                        'initialValue' => '',

                        'isCanAddField' => false,

                    ],
                    [
                        'title' => 'type',
                        'entityType' => 'infogroup',
                        'name' => 'type',
                        'apiName' => 'type',
                        'type' =>  'string',
                        'validation' => 'required|max:255',
                        'initialValue' => '',

                        'isCanAddField' => false,

                    ],
                    [
                        'title' => 'productType',
                        'entityType' => 'infoblock',
                        'name' => 'productType',
                        'apiName' => 'productType',
                        'type' =>  'number',
                        'validation' => 'required|max:255',
                        'initialValue' => '',

                        'isCanAddField' => false,

                    ],
                  
                   
                ],
                'relations' => [],
            ]]
        ];
    }
    
}
