<?php

namespace App\Models;

use App\Http\Resources\BtxCompanyResource;
use App\Http\Resources\BtxContactResource;
use App\Http\Resources\BtxDealResource;
use App\Http\Resources\BtxLeadResource;
use App\Http\Resources\SmartResource;
use App\Models\Bitrix\BitrixApp;
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
    public function bitrixApps()
    {
        return $this->hasMany(BitrixApp::class);
    }


    public function bxRq()
    {
        return $this->hasMany(BxRq::class);
    }


    public function providers()
    {
        return $this->hasMany(Agent::class, 'portalId', 'id');
    }

    public function transcriptions()
    {
        return $this->hasMany(Transcription::class, 'portal_id', 'id');
    }


    public function templates()
    {
        return $this->hasMany(Template::class, 'portalId', 'id');
    }

    public function callingGroups()
    {
        return $this->hasMany(Calling::class);
    }
    public function smarts()
    {
        return $this->hasMany(Smart::class);
    }
    public function lists()
    {
        return $this->hasMany(Bitrixlist::class);
    }
    public function deals()
    {
        return $this->hasMany(BtxDeal::class);
    }


    public function companies()
    {
        return $this->hasMany(BtxCompany::class);
    }
    public function contacts()
    {
        return $this->hasMany(BtxContact::class);
    }
    public function leads()
    {
        return $this->hasMany(BtxLead::class);
    }

    public function rpas()
    {
        return $this->hasMany(BtxRpa::class);
    }



    public function departaments()
    {
        return $this->hasMany(Departament::class);
    }
    public function timezones()
    {
        return $this->hasMany(Timezone::class);
    }
    //todo
    // smart smartId stages categories
    // departament - id группы сотрудников - по идее может быть в итоге несколько групп hasMany
    // callings tasks - taskGroupId hasMany
    // report 
    // lists - универсальные списки id списка по идее списков может стать много kpi отказы звонки и тд
    // hasMany у списков должен быть type (kpi, invoices, conversions)


    public function getSalesCallingGroupId()
    {
        return $this->callingGroups()->first();
    }
    public function getSalesBitrixListId()
    {
        return $this->lists()->first();
    }
    public function getSalesDepartamentId()
    {
        return $this->departaments()->first();
    }
    public function getSalesTimezone()
    {
        return $this->timezones()->first();
    }
    public function getSalesSmart()
    {
        $smart =  $this->smarts()->first();
        if ($smart) {
            $smart = new SmartResource($smart);
        }

        return $smart;
    }
    public function getSalesSmarts()
    {
        $smarts =  $this->smarts()->where('group', 'sales')
            ->orWhere('group', 'tmc')->get();

        $resultSmarts    = [];


        if (!empty($smarts)) {
            foreach ($smarts as $smart) {

                if ($smart) {
                    $smartResource = new SmartResource($smart);
                    array_push($resultSmarts, $smart);
                }
            }
        }



        return $resultSmarts;
    }

    // Метод для получения первой сделки
    public function smart()
    {
        $smart = $this->smarts()->first();
        if ($smart) {
            $smart = new SmartResource($smart);
        } else {
            $smart = null;
        }

        return  $this->smarts()->first(); // или latest() в зависимости от того, какой элемент считается "первым"
    }


    // Метод для получения первой сделки
    public function deal()
    {
        $deal = $this->deals()->first();
        if ($deal) {
            $deal = new BtxDealResource($deal);
        } else {
            $deal = null;
        }

        return  $deal; // или latest() в зависимости от того, какой элемент считается "первым"
    }

    public function company()
    {
        $company = $this->companies()->first();
        if ($company) {
            $company = new BtxCompanyResource($company);
        } else {
            $company = null;
        }

        return  $company;
    }


    public function contact()
    {
        $contact = $this->contacts()->first();
        if ($contact) {
            $contact = new BtxContactResource($contact);
        } else {
            $contact = null;
        }

        return  $contact;
    }

    public function lead()
    {
        $lead = $this->leads()->first();

        if ($lead) {
            $lead = new BtxLeadResource($lead);
        } else {
            $lead = null;
        }


        return  $lead;
    }

    public function bitrixLists()
    {
        return $this->lists;
    }



    public function measures()
    {
        return $this->hasMany(PortalMeasure::class);
    }

    public function contracts()
    {
        return $this->hasMany(PortalContract::class);
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
            'apiName' => 'portal',
            'title' => 'Создание Портала',
            'entityType' => 'entity',
            'groups' => [[
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
            ],]
        ];
    }
}
