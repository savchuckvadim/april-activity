<?php

namespace App\Models\Garant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Infoblock extends Model
{
    use HasFactory;

    public function complects()
    {
        return $this->belongsToMany(Complect::class, 'complect_infoblock');
    }

    // Отношение: Инфоблок принадлежит одной группе
    public function group()
    {
        return $this->belongsTo(InfoGroup::class, 'group_id');
    }

      /**
     * Получение родительского инфоблока (если есть).
     */
    public function parentPackage()
    {
        return $this->belongsTo(Infoblock::class, 'parent_id');
    }

    public function inPackage()
    {
        return $this->hasMany(Infoblock::class, 'parent_id');
    }

    /**
     * Пакеты (если инфоблок является частью пакета).
     */
    // public function package()
    // {
    //     return $this->belongsTo(Infoblock::class, 'parent_id')->where('isPackage', true);
    // }
    protected $fillable = [
        'number',
        'name',
        'code',
        'title',
        'description',
        'descriptionForSale',
        'shortDescription',
        'weight',
        'inGroupId',
        'groupId',
        'isLa',
        'isFree',
        'isShowing',
        'isSet',
    ];

    public static function getForm()
    {

        return [
            'apiName' => 'infoblock',
            'title' => 'Инфоблок',
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
                        'entityType' => 'infoblock',
                        'name' => 'name',
                        'apiName' => 'name',
                        'type' =>  'string',
                        'validation' => 'required|max:255',
                        'initialValue' => '',

                        'isCanAddField' => false,

                    ],
                    [
                        'title' => 'title',
                        'entityType' => 'infoblock',
                        'name' => 'title',
                        'apiName' => 'title',
                        'type' =>  'string',
                        'validation' => 'required|max:255',
                        'initialValue' => '',

                        'isCanAddField' => false,

                    ],
                    [
                        'title' => 'number',
                        'entityType' => 'infoblock',
                        'name' => 'name',
                        'apiName' => 'number',
                        'type' =>  'number',
                        'validation' => 'required|max:255',
                        'initialValue' => '',

                        'isCanAddField' => false,

                    ],
                    [
                        'title' => 'Код для ассоциаций',
                        'entityType' => 'infoblock',
                        'name' => 'code',
                        'apiName' => 'code',
                        'type' =>  'string',
                        'validation' => 'required|max:255',
                        'initialValue' => '',

                        'isCanAddField' => false,

                    ],


                    [
                        'title' => 'description',
                        'entityType' => 'infoblock',
                        'name' => 'description',
                        'apiName' => 'description',
                        'type' =>  'text',
                        'validation' => 'required|max:255',
                        'initialValue' => '',

                        'isCanAddField' => false,

                    ],
                    [
                        'title' => 'descriptionForSale',
                        'entityType' => 'infoblock',
                        'name' => 'descriptionForSale',
                        'apiName' => 'descriptionForSale',
                        'type' =>  'text',
                        'validation' => 'required|max:255',
                        'initialValue' => '',

                        'isCanAddField' => false,

                    ],
                    [
                        'title' => 'shortDescription',
                        'entityType' => 'infoblock',
                        'name' => 'shortDescription',
                        'apiName' => 'shortDescription',
                        'type' =>  'text',
                        'validation' => 'required|max:255',
                        'initialValue' => '',

                        'isCanAddField' => false,

                    ],
                    [
                        'title' => 'weight',
                        'entityType' => 'infoblock',
                        'name' => 'weight',
                        'apiName' => 'weight',
                        'type' =>  'string',
                        'validation' => 'required|max:255',
                        'initialValue' => '',

                        'isCanAddField' => false,

                    ],
                    [
                        'title' => 'inGroupId',
                        'entityType' => 'infoblock',
                        'name' => 'inGroupId',
                        'apiName' => 'inGroupId',
                        'type' =>  'number',
                        'validation' => 'required|max:255',
                        'initialValue' => '',

                        'isCanAddField' => false,

                    ],
                    [
                        'title' => 'groupId',
                        'entityType' => 'infoblock',
                        'name' => 'groupId',
                        'apiName' => 'groupId',
                        'type' =>  'number',
                        'validation' => 'required|max:255',
                        'initialValue' => 'foreignId',

                        'isCanAddField' => false,

                    ],
                    [
                        'title' => 'Является судебной практикой ?',
                        'entityType' => 'infoblock',
                        'name' => 'isLa',
                        'apiName' => 'isLa',
                        'type' =>  'boolean',
                        'validation' => '',
                        'initialValue' => false,
                        'isCanAddField' => false,

                    ],
                    [
                        'title' => 'Является бесплатным блоком ?',
                        'entityType' => 'infoblock',
                        'name' => 'isFree',
                        'apiName' => 'isFree',
                        'type' =>  'boolean',
                        'validation' => '',
                        'initialValue' => false,
                        'isCanAddField' => false,

                    ],
                    [
                        'title' => 'Показывается во фронтенде ?',
                        'entityType' => 'infoblock',
                        'name' => 'isShowing',
                        'apiName' => 'isShowing',
                        'type' =>  'boolean',
                        'validation' => '',
                        'initialValue' => false,
                        'isCanAddField' => false,

                    ],
                    [
                        'title' => 'Является набором блоков ?',
                        'entityType' => 'infoblock',
                        'name' => 'isSet',
                        'apiName' => 'isSet',
                        'type' =>  'boolean',
                        'validation' => '',
                        'initialValue' => false,
                        'isCanAddField' => false,

                    ],

                ],
                'relations' => [],
            ]]
        ];
    }
}
