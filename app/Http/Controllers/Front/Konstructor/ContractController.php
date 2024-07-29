<?php

namespace App\Http\Controllers\Front\Konstructor;

use App\Http\Controllers\APIController;
use App\Http\Controllers\BitrixController;
use App\Http\Controllers\Controller;
use App\Http\Resources\PortalContractResource;
use App\Models\Bitrixfield;
use App\Models\BitrixfieldItem;
use App\Models\Portal;
use App\Models\PortalContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ContractController extends Controller
{

    public function frontInit(Request $request) //by id
    {
        $data = $request->all();
        $domain = $data['domain'];
        $companyId = $data['companyId'];
        $resultClientRq = null;
        $resultClientAdressRq = null;
        $resultClientBankRq = null;

        try {
            $portal = Portal::where('domain', $domain)->first();

            $providers = $portal->providers;
            $hook = BitrixController::getHook($domain);
            $result = [
                'providers' => $providers,
                'client' =>  [
                    'rq' => [],
                    'bank' => [],
                    'address' => [],
                ],
                'provider' => [
                    'rq' => [],
                    'bank' => [],
                    'address' => [],
                ],
            ];
            $rqMethod = '/crm.requisite.list';
            $rqData = [
                'filter' => [
                    'ENTITY_TYPE_ID' => 4,
                    'ENTITY_ID' => $companyId,
                ]
            ];
            $url = $hook . $rqMethod;
            $responseData = Http::post($url,  $rqData);
            $result['client']['rq']  = BitrixController::getBitrixResponse($responseData, $rqMethod);


            //bank
            if (!empty($result['client']['rq'])) {
                $clientRq = $result['client']['rq'][0];
                if (!empty($clientRq) && isset($clientRq['ID'])) {


                    $result['client']['rq']  = $clientRq;
                    $rqId = $result['client']['rq']['ID'];
                    $bankMethod = '/crm.requisite.bankdetail.list';
                    $bankData = [
                        'filter' => [
                            // 'ENTITY_TYPE_ID' => 4,
                            'ENTITY_ID' => $rqId,
                        ]
                    ];
                    $url = $hook . $bankMethod;
                    $responseData = Http::post($url,  $bankData);
                    $result['client']['bank']  = BitrixController::getBitrixResponse($responseData, $bankMethod);
                    if (!empty($result['client']['bank'])) {
                        if (isset($result['client']['bank'][0])) {
                            $result['client']['bank'] = $result['client']['bank'][0];
                        }
                    }

                    //address
                    $addressMethod = '/crm.address.list';
                    $addressData = [
                        'filter' => [
                            'ENTITY_TYPE_ID' => 8,
                            'ENTITY_ID' =>  $rqId,
                        ]
                    ];
                    $url = $hook . $addressMethod;
                    $responseData = Http::post($url,  $addressData);
                    $result['client']['address']  = BitrixController::getBitrixResponse($responseData, $addressMethod);
                    // {
                    //     "ID": 1,
                    //     "NAME": "Фактический адрес"
                    // },
                    // {
                    //     "ID": 4,
                    //     "NAME": "Адрес регистрации"
                    // },
                    // {
                    //     "ID": 6,
                    //     "NAME": "Юридический адрес"
                    // },
                    // {
                    //     "ID": 9,
                    //     "NAME": "Адрес бенефициара"
                    // }
                }
            }
            $client = $this->getClientRqForm($result['client']['rq'], $result['client']['address'], $result['client']['address']);
            $result['client'] = $client;
            return APIController::getSuccess(
                ['init' => $result,]
            );
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                ['companyId' => $companyId, 'domain' => $domain]
            );
        }
    }

    public function get($portalContractId) //by id
    {
        try {
            $portalContract = PortalContract::find($portalContractId);

            if ($portalContract) {
                // $resultSmart = new SmartResource($rpa);
                return APIController::getSuccess(
                    ['portalcontract' => $portalContract]
                );
            } else {
                return APIController::getError(
                    'portalcontract was not found',
                    ['portalcontract' => $portalContract]
                );
            }
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                ['portalcontractId' => $portalContract]
            );
        }
    }

    public  function getAll()
    {

        // Создание нового Counter
        $portalcontracts = PortalContract::all();
        if ($portalcontracts) {

            return APIController::getSuccess(
                ['portalcontracts' => $portalcontracts]
            );
        }


        return APIController::getSuccess(
            ['portalcontracts' => []]
        );
    }

    public function getByPortal(Request $request)
    {
        $domain =  $request->domain;
        $portalcontracts = [];
        $resultContracts = [];
        // Создание нового Counter
        $portal = Portal::where('domain', $domain)->first();
        if ($portal) {
            $portalcontracts = $portal->contracts;
            if (!empty($portalcontracts)) {

                foreach ($portalcontracts as $portalcontract) {
                    $resultContract = new PortalContractResource($portalcontract);



                    if (!empty($portalcontract['contract'])) {


                        // if (empty($portalcontract['productName'])) {
                        //     $resultContract['productName'] = $portalcontract['contract']['productName'];
                        // }
                        // if (!empty($portalcontract['portal_measure'])) {

                        //     if (!empty($portalcontract['portal_measure']['bitrixId'])) {

                        //         $resultContract['bitrixMeasureId'] = (int)$portalcontract['portal_measure']['bitrixId'];
                        //     }
                        // }
                    }

                    // $fieldItem = BitrixfieldItem::find($portalcontract['bitrixfield_item_id']);
                    // $field = Bitrixfield::find($fieldItem['bitrixfield_id']);


                    // $portalcontract['code'] = $portalcontract['contract']['code'];
                    // $portalcontract['shortName'] = $portalcontract['contract']['code'];
                    // $portalcontract['number'] = $portalcontract['contract']['number'];


                    // $resultContract['fieldItem'] = $fieldItem;
                    // $resultContract['field'] = $field;
                    // $portalcontract['aprilName'] =  $portalcontract;
                    // $resultContract['bitrixName'] =  $fieldItem['title'];
                    // $portalcontract['discount'] = (int)$portalcontract['contract']['discount'];
                    // $portalcontract['prepayment'] = (int)$portalcontract['contract']['prepayment'];

                    // $resultContract['itemId'] =  $fieldItem['bitrixId'];

                    array_push($resultContracts, $resultContract);
                }




                return APIController::getSuccess(
                    ['contracts' => $resultContracts]
                );
            }
        }


        return APIController::getSuccess(
            ['contracts' => $portalcontracts, 'portal' => $portal]
        );
    }



    //utils
    protected function getClientRqForm($bxRq, $bxAdressRq, $bxBankRq)
    {
        $result = [
            'rq' => [
                [
                    'type' => 'select',
                    'name' => 'Тип клиента',
                    'value' =>  [
                        'id' => 0,
                        'code' => 'org',
                        'name' => 'Организация Коммерческая',
                        'title' => 'Организация Коммерческая'
                    ],
                    'isRequired' => true,
                    'code' => 'type',
                    'items' => [
                        [
                            'id' => 0,
                            'code' => 'org',
                            'name' => 'Организация Коммерческая',
                            'title' => 'Организация Коммерческая'
                        ],
                        [
                            'id' => 1,
                            'code' => 'org_state',
                            'name' => 'Организация Бюджетная',
                            'title' => 'Организация Бюджетная'
                        ],
                        [
                            'id' => 2,
                            'code' => 'ip',
                            'name' => 'Индивидуальный предприниматель',
                            'title' => 'Индивидуальный предприниматель'
                        ],
                        [
                            'id' => 3,
                            'code' => 'advokat',
                            'name' => 'Адвокат',
                            'title' => 'Адвокат'
                        ],
                        [
                            'id' => 4,
                            'code' => 'fiz',
                            'name' => 'Физическое лицо',
                            'title' => 'Физическое лицо'
                        ],

                    ],
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'group' => 'base',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 0,

                ],
                [
                    'type' => 'string',
                    'name' => 'Полное наименование организации',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'fullname',
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'group' => 'header',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 1,



                ],
                [
                    'type' => 'string',
                    'name' => 'Сокращенное наименование организации',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'shortname',
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'group' => 'header',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 2,


                ],
                [
                    'type' => 'string',
                    'name' => 'Роль клиента в договоре',
                    'value' => 'Заказчик',
                    'isRequired' => true,
                    'code' => 'role',
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'group' => 'header',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 3

                ],
                [
                    'type' => 'string',
                    'name' => 'Должность руководителя организации',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'position',
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'group' => 'header',
                    'isActive' => true,
                    'order' => 4
                ],
                [
                    'type' => 'string',
                    'name' => 'Должность руководителя организации (в лице)',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'position_case',
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'group' => 'header',
                    'isActive' => true,
                    'order' => 6,

                ],
                [
                    'type' => 'string',
                    'name' => 'ФИО руководителя организации',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'director',
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'group' => 'header',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 5,

                ],
                [
                    'type' => 'string',
                    'name' => 'ФИО руководителя организации (в лице)',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'director_case',
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'group' => 'header',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 7

                ],

                [
                    'type' => 'string',
                    'name' => 'Действующий на основании',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'based',
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'group' => 'header',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 8

                ],
                

                [
                    'type' => 'string',
                    'name' => 'ИНН',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'inn',
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'group' => 'rq',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 9

                ],
                [
                    'type' => 'string',
                    'name' => 'КПП',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'kpp',
                    'includes' => ['org', 'org_state'],
                    'group' => 'rq',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 10

                ],

                [
                    'type' => 'string',
                    'name' => 'ОГРН',
                    'value' => '',
                    'isRequired' => false,
                    'code' => 'ogrn',
                    'includes' => ['org', 'org_state'],
                    'group' => 'rq',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 11

                ],

                [
                    'type' => 'string',
                    'name' => 'ОГРНИП',
                    'value' => '',
                    'isRequired' => false,
                    'code' => 'ogrnip',
                    'includes' => ['ip'],
                    'group' => 'rq',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 12

                ],

                [
                    'type' => 'string',
                    'name' => 'ФИО главного бухгалтера организации',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'accountant',
                    'includes' => ['org', 'org_state'],
                    'group' => 'rq',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 13

                ],
                [
                    'type' => 'string',
                    'name' => 'ФИО ответственного за получение справочника',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'assigned',
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'group' => 'rq',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 14

                ],
                [
                    'type' => 'string',
                    'name' => 'Телефон ответственного за получение справочника',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'assignedPhone',
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'group' => 'rq',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 15

                ],

                [
                    'type' => 'string',
                    'name' => 'Телефон',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'phone',
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'group' => 'rq',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 16

                ],
                [
                    'type' => 'string',
                    'name' => 'E-mail',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'email',
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'group' => 'rq',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 17

                ],
                [
                    'type' => 'string',
                    'name' => 'ФИО',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'personName',
                    'includes' => ['fiz', 'advokat'],
                    'group' => 'header',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 0

                ],


                [
                    'type' => 'string',
                    'name' => 'Вид Документа',
                    'value' => 'Паспорт',
                    'isRequired' => true,
                    'code' => 'document',
                    'includes' => ['fiz', 'advokat'],
                    'group' => 'rq',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 1

                ],
                [
                    'type' => 'string',
                    'name' => 'Серия Документа',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'docSer',
                    'includes' => ['fiz', 'advokat'],
                    'group' => 'rq',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 2

                ],
                [
                    'type' => 'string',
                    'name' => 'Номер Документа',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'docNum',
                    'includes' => ['fiz', 'advokat'],
                    'group' => 'rq',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 3

                ],
                [
                    'type' => 'string',
                    'name' => 'Дата Выдачи Документа',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'docDate',
                    'includes' => ['fiz', 'advokat'],
                    'group' => 'rq',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 4

                ],
                [
                    'type' => 'string',
                    'name' => 'Документ выдан подразделением',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'docDate',
                    'includes' => ['fiz', 'advokat'],
                    'group' => 'rq',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 5

                ],
                [
                    'type' => 'string',
                    'name' => 'Код подразделения',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'docDate',
                    'includes' => ['fiz', 'advokat'],
                    'group' => 'rq',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 6

                ],
                [
                    'type' => 'text',
                    'name' => 'Прочие реквизиты',
                    'value' => '',
                    'isRequired' => false,
                    'code' => 'other',
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'group' => 'rq',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 20


                ],


            ],
            'address' => [
                [
                    'type' => 'string',
                    'name' => 'Юридический адрес',
                    'value' => '',
                    'isRequired' => false,
                    'code' => 'registredAdress',
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'group' => 'rq',
                    'isActive' => true,
                    'isDisable' => false,

                ],
                [
                    'type' => 'string',
                    'name' => 'Юридический адрес',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'registredAdress',
                    'includes' => ['org', 'org_state', 'ip'],
                    'group' => 'rq',
                    'isActive' => true,
                    'isDisable' => false,

                ],
                [
                    'type' => 'string',
                    'name' => 'Адрес прописки',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'registredAdress',
                    'includes' => ['advokat', 'fiz'],
                    'group' => 'rq',
                    'isActive' => true,
                    'isDisable' => false,

                ],
                [
                    'type' => 'string',
                    'name' => 'Фактический адрес',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'registredAdress',
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'group' => 'rq',
                    'isActive' => true,
                    'isDisable' => false,

                ],
            ],
            'bank' => [
                [
                    'type' => 'string',
                    'name' => 'Наименование банка',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'bank',
                    'includes' => ['org', 'org_state', 'ip', 'advokat'],
                    'group' => 'bank',
                    'isActive' => true,
                    'isDisable' => false,
                ],
                [
                    'type' => 'string',
                    'name' => 'БИК',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'bik',
                    'includes' => ['org', 'org_state', 'ip', 'advokat'],
                    'group' => 'bank',
                    'isActive' => true,
                    'isDisable' => false,
                ],
                [
                    'type' => 'string',
                    'name' => 'р/с',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'rs',
                    'includes' => ['org', 'org_state', 'ip', 'advokat'],
                    'group' => 'bank',
                    'isActive' => true,
                    'isDisable' => false,
                ],
                [
                    'type' => 'string',
                    'name' => 'к/с',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'ks',
                    'includes' => ['org', 'org_state', 'ip', 'advokat'],
                    'group' => 'bank',
                    'isActive' => true,
                    'isDisable' => false,
                ],
                [
                    'type' => 'string',
                    'name' => 'Прочие банковские реквизиты',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'bankOther',
                    'includes' => ['org', 'org_state', 'ip', 'advokat'],
                    'group' => 'bank',
                    'isActive' => true,
                    'isDisable' => false,
                ],
            ]
        ];

        foreach ($bxRq as $bxRqFieldName => $value) {
            switch ($bxRqFieldName) {
                case 'NAME': //Название реквизита. Обязательное поле.

                    break;
                case 'RQ_NAME': //Ф.И.О.  физлица ип
                    foreach ($result['rq'] as $rq) {
                        if ($rq['code'] === 'fullname') {
                            $rq['value'] = $value;
                        }

                        if ($rq['code'] === 'personName') {
                            $rq['value'] = $value;
                        }
                    }
                    break;
                case 'RQ_COMPANY_NAME': //Сокращенное наименование организации.
                    foreach ($result['rq'] as $rq) {
                        if ($rq['code'] === 'name') {
                            $rq['value'] = $value;
                        }
                    }
                    break;
                case 'RQ_COMPANY_FULL_NAME':
                    foreach ($result['rq'] as $rq) {
                        if ($rq['code'] === 'fullname') {
                            $rq['value'] = $value;
                        }
                    }
                    break;
                case 'RQ_DIRECTOR': //Ген. директор.
                    foreach ($result['rq'] as $rq) {
                        if ($rq['code'] === 'director') {
                            $rq['value'] = $value;
                        }
                    }
                    break;
                case 'RQ_ACCOUNTANT': // Гл. бухгалтер.
                    foreach ($result['rq'] as $rq) {
                        if ($rq['code'] === 'accountant') {
                            $rq['value'] = $value;
                        }
                    }
                    break;
                case 'RQ_EMAIL':
                    foreach ($result['rq'] as $rq) {
                        if ($rq['code'] === 'email') {
                            $rq['value'] = $value;
                        }
                    }
                    break;
                case 'RQ_PHONE':
                    foreach ($result['rq'] as $rq) {
                        if ($rq['code'] === 'phone') {
                            $rq['value'] = $value;
                        }
                    }
                    break;


                    //fiz lic
                case 'RQ_IDENT_DOC': //Вид документа.
                    foreach ($result['rq'] as $rq) {
                        if ($rq['code'] === 'document') {
                            $rq['value'] = $value;
                        }
                    }
                    break;
                case 'RQ_IDENT_DOC_SER': //Серия.
                    foreach ($result['rq'] as $rq) {
                        if ($rq['code'] === 'docSer') {
                            $rq['value'] = $value;
                        }
                    }
                    break;
                case 'RQ_IDENT_DOC_NUM': // Номер.
                    foreach ($result['rq'] as $rq) {
                        if ($rq['code'] === 'docNum') {
                            $rq['value'] = $value;
                        }
                    }
                    break;
                case 'RQ_IDENT_DOC_DATE': //Дата выдачи.
                    foreach ($result['rq'] as $rq) {
                        if ($rq['code'] === 'docDate') {
                            $rq['value'] = $value;
                        }
                    }
                    break;

                case 'RQ_IDENT_DOC_ISSUED_BY': //Кем выдан.
                    foreach ($result['rq'] as $rq) {
                        if ($rq['code'] === 'docIssuedBy') {
                            $rq['value'] = $value;
                        }
                    }
                    break;

                case 'RQ_IDENT_DOC_DEP_CODE': //Код подразделения
                    foreach ($result['rq'] as $rq) {
                        if ($rq['code'] === 'docDepCode') {
                            $rq['value'] = $value;
                        }
                    }
                    break;


                case 'RQ_INN':
                    foreach ($result['rq'] as $rq) {
                        if ($rq['code'] === 'inn') {
                            $rq['value'] = $value;
                        }
                    }
                    break;

                case 'RQ_KPP':
                    foreach ($result['rq'] as $rq) {
                        if ($rq['code'] === 'kpp') {
                            $rq['value'] = $value;
                        }
                    }
                    break;

                case 'RQ_OGRN':
                    foreach ($result['rq'] as $rq) {
                        if ($rq['code'] === 'ogrn') {
                            $rq['value'] = $value;
                        }
                    }
                    break;


                case 'RQ_OGRNIP':
                    foreach ($result['rq'] as $rq) {
                        if ($rq['code'] === 'ogrnip') {
                            $rq['value'] = $value;
                        }
                    }
                    break;


                case 'RQ_OKPO':
                    # code...
                    break;

                case 'RQ_OKTMO':
                    # code...
                    break;

                case 'RQ_OKVED':
                    # code...
                    break;

                default:
                    # code...
                    break;
            }
        }

        // $isRegisrted = false;
        // $isFizRegisrted = false;
        // if($bxAdressRq['TYPE_ID'] === 6){
        //     $isRegisrted = true;
        // }else  if($bxAdressRq['TYPE_ID'] === 4){
        //     $isFizRegisrted = true;
        // }


        // foreach ($bxAdressRq as $bxAdressRqFieldName => $value) {
        //     $isRegisrted = true;
        //     if($bxAdressRqFieldName === 'TYPE_ID'){

        //         if($value === 1 || $value === 2 || $value === 3 ){
        //             $isRegisrted = false;

        //         }
        //     }
        //     switch ($bxRqFieldName) {
        //         case 'NAME': //Название реквизита. Обязательное поле.
        //             foreach ($result['rq'] as $rq) {
        //                 if ($rq['code'] === 'name') {
        //                     $rq['value'] = $value;
        //                 }
        //             }
        //             break;
        //         case 'RQ_NAME': //Ф.И.О.  физлица ип
        //             # code...
        //             break;
        //         case 'RQ_COMPANY_NAME': //Сокращенное наименование организации.
        //             # code...
        //             break;
        //         case 'RQ_COMPANY_FULL_NAME':
        //             # code...
        //             break;
        //         case 'RQ_DIRECTOR': //Ген. директор.
        //             # code...
        //             break;
        //         case 'RQ_ACCOUNTANT': // Гл. бухгалтер.
        //             # code...
        //             break;
        //         case 'RQ_EMAIL':
        //             # code...
        //             break;
        //         case 'RQ_PHONE':
        //             # code...
        //             break;


        //             //fiz lic
        //         case 'RQ_IDENT_DOC': //Вид документа.
        //             # code...
        //             break;
        //         case 'RQ_IDENT_DOC_SER': //Серия.
        //             # code...
        //             break;
        //     }
        // }


        // foreach ($bxBankRq as $bxAdressRqFieldName => $value) {
        //     switch ($bxRqFieldName) {
        //         case 'NAME': //Название реквизита. Обязательное поле.
        //             # code...
        //             break;
        //         case 'RQ_NAME': //Ф.И.О.  физлица ип
        //             # code...
        //             break;
        //         case 'RQ_COMPANY_NAME': //Сокращенное наименование организации.
        //             # code...
        //             break;
        //         case 'RQ_COMPANY_FULL_NAME':
        //             # code...
        //             break;
        //         case 'RQ_DIRECTOR': //Ген. директор.
        //             # code...
        //             break;
        //         case 'RQ_ACCOUNTANT': // Гл. бухгалтер.
        //             # code...
        //             break;
        //         case 'RQ_EMAIL':
        //             # code...
        //             break;
        //         case 'RQ_PHONE':
        //             # code...
        //             break;


        //             //fiz lic
        //         case 'RQ_IDENT_DOC': //Вид документа.
        //             # code...
        //             break;
        //         case 'RQ_IDENT_DOC_SER': //Серия.
        //             # code...
        //             break;
        //     }
        // }
        return $result;
    }
    protected function getContractGeneralForm()
    {

        return [
            [
                'type' => 'date',
                'name' => 'Договор от',
                'value' => '',
                'isRequired' => true,
                'code' => 'contract_create_date'

            ],
            [
                'type' => 'date',
                'name' => 'Действие договора с',
                'value' => '',
                'isRequired' => true,
                'code' => 'contract_start'

            ],
            [
                'type' => 'date',
                'name' => 'Действие договора по',
                'value' => '',
                'isRequired' => true,
                'code' => 'contract_finish'

            ],
            [
                'type' => 'date',
                'name' => 'Действие договора с',
                'value' => '',
                'isRequired' => true,
                'code' => 'prepayment_start'

            ],
            [
                'type' => 'date',
                'name' => 'Действие договора по',
                'value' => '',
                'isRequired' => true,
                'code' => 'prepayment_finish'

            ],
            [
                'type' => 'money',
                'name' => 'Действие договора по',
                'value' => '',
                'isRequired' => true,
                'code' => 'prepayment_sum'

            ],
            [
                'type' => 'money',
                'name' => 'Действие договора по',
                'value' => '',
                'isRequired' => true,
                'code' => 'month_sum'

            ],
        ];
    }

    protected function getContractHeaderText()
    {
        $headerText = '{client_fullname}, официальный партнер компании "Гарант", 
        именуемый в дальнейшем "Исполнитель", в лице {MyCompanyRequisiteRqDirector~Case=0}, 
        в должности: {MyCompanyRequisiteUfCrm1689675296}, действующего(-ей) на основании 
        {MyCompanyRequisiteUfCrm1689675325} с одной стороны и   {RequisiteRqCompanyFullName}, 
        именуемое(-ый) в дальнейшем "Заказчик",  
        в лице {CompanyRequisiteRqDirector~Case=0},  
        в должности: {RequisiteUfCrm1689675296}, 
        действующего(-ей) на основании {RequisiteUfCrm1689675325} 
        с другой стороны, заключили настоящий Договор о нижеследующем:';
        return $headerText;
    }
}
