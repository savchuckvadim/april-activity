<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;



class Portal extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'domain',
        'key',
        'C_REST_CLIENT_ID',
        'C_REST_CLIENT_SECRET',
        'C_REST_WEB_HOOK_URL',
    ];


    //relation
    public function providers()
    {
        return $this->hasMany(Agent::class, 'portalId', 'id');
    }


    public function templates()
    {
        return $this->hasMany(Template::class, 'portalId', 'id');
    }





    //crypto
    public function getKey()
    {
        return Crypt::decryptString($this->key);
    }

    public function getClientId()
    {
        return Crypt::decryptString($this->C_REST_CLIENT_ID);
    }

    public function getSecret()
    {
        return Crypt::decryptString($this->C_REST_CLIENT_SECRET);
    }
    public function getHook()
    {
        return Crypt::decryptString($this->C_REST_WEB_HOOK_URL);
    }

    //for create

    public static function getForm()
    {

        return [
            [
                'groupName' => 'Создание портала',
                'type' => 'portal',
                'isCanAddField' => false,
                'isCanDeleteField' => false, //нельзя удалить ни одно из инициальзационных fields
                'isRequired' => true, //хотя бы одно поле в шаблоне должно быть
                'fields' => [
                    [
                        'title' => 'Номер из firebase',
                        'name' => 'number',
                        'apiName' => 'number',
                        'type' =>  'number',
                        'validation' => 'required|max:255',
                        'initialValue' => '',
                        'isCanAddField' => false,
                    ],
                    [
                        'title' => 'Домен',
                        'name' => 'domain',
                        'apiName' => 'domain',
                        'type' =>  'string',
                        'validation' => 'required|max:255',
                        'initialValue' => '',
                        'isCanAddField' => false,
                    ],
                    [
                        'title' => 'Ключ от хука',
                        'name' => 'key',
                        'apiName' => 'key',
                        'type' =>  'string',
                        'validation' => 'required|max:255',
                        'initialValue' => '',
                        'isCanAddField' => false,
                    ],
                    [
                        'title' => 'Клиент id приложения в битрикс',

                        'name' => 'C_REST_CLIENT_ID',
                        'apiName' => 'clientId',
                        'type' =>  'string',
                        'validation' => 'required|max:255',
                        'initialValue' => '',
                        'isCanAddField' => false,
                    ],
                    [
                        'title' => 'Secret приложения в битрикс',

                        'name' => 'C_REST_CLIENT_SECRET',
                        'apiName' => 'clientSecret',
                        'type' =>  'string',
                        'validation' => 'required|max:255',
                        'initialValue' => '',
                        'isCanAddField' => false,
                    ],
                    [
                        'title' => 'Юзер / ключ хука',

                        'name' => 'C_REST_WEB_HOOK_URL',
                        'apiName' => 'hook',
                        'type' =>  'string',
                        'validation' => 'required|max:255',
                        'initialValue' => '',
                        'isCanAddField' => false,
                    ]
                ]
            ],
        ];
    }
}
