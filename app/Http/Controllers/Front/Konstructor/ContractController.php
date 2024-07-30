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
use Illuminate\Support\Facades\Log;

class ContractController extends Controller
{

    public function frontInit(Request $request) //by id
    {
        $data = $request->all();
        $domain = $data['domain'];
        $companyId = $data['companyId'];
        $contractType = $data['contractType'];


        $contract = $data['contract'];
        $contractQuantity = $contract['coefficient'];

        $productSet = $data['productSet'];
        $products = $data['products'];
        $productName = $contract['productName'];
        $arows = $data['arows'];

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
                'contract' => $this->getContractGeneralForm($arows, $contractQuantity)
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

            $rqResponse = BitrixController::getBitrixResponse($responseData, $rqMethod);


            //bank
            if (!empty($rqResponse)) {
                $clientRq = $rqResponse[0];
                if (!empty($clientRq) && isset($clientRq['ID'])) {


                    $rqResponse  = $clientRq;
                    $rqId = $rqResponse['ID'];
                    $bankMethod = '/crm.requisite.bankdetail.list';
                    $bankData = [
                        'filter' => [
                            // 'ENTITY_TYPE_ID' => 4,
                            'ENTITY_ID' => $rqId,
                        ]
                    ];
                    $url = $hook . $bankMethod;
                    $responseData = Http::post($url,  $bankData);
                    $clientRqBank  = BitrixController::getBitrixResponse($responseData, $bankMethod);
                    if (!empty($clientRqBank)) {
                        if (isset($clientRqBank[0])) {
                            $clientRqBank = $clientRqBank[0];
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
                    $clientRqAddress  = BitrixController::getBitrixResponse($responseData, $addressMethod);
                }

                $client = $this->getClientRqForm($clientRq, $clientRqAddress, $clientRqBank, $contractType);
                $result['client'] = $client;
                return APIController::getSuccess(
                    [
                        'init' => $result,
                        'addressresponse' => $result['client']['address'],
                        'clientRq' => $clientRq,
                        'clientRqBank' => $clientRqBank,
                        'clientRqAddress' => $clientRqAddress,
                    ]
                );
            }
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                ['companyId' => $companyId, 'domain' => $domain, 'addressresponse' => $result['client']['address']]
            );
        }
    }

    public function getContractDocument(Request $request)
    {
        $data = $request->all();
        return APIController::getSuccess(
            ['contractData' => $data, 'link' => $data]
        );
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
    protected function getClientRqForm($bxRq, $bxAdressesRq, $bxBankRq, $contractType)
    {
        $clientRole = 'Заказчик';

        switch ($contractType) {
            case 'abon':
            case 'key':
                $clientRole = 'Покупатель';
                break;
            case 'lic':
                $clientRole = 'Лицензиат';
                break;
            default:
                $clientRole = 'Заказчик';
                # code...
                break;
        }
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
                    'group' => 'rq',
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
                    'group' => 'rq',
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
                    'group' => 'rq',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 2,


                ],
                [
                    'type' => 'string',
                    'name' => 'Роль клиента в договоре',
                    'value' => $clientRole,
                    'isRequired' => true,
                    'code' => 'role',
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'group' => 'rq',
                    'isActive' => true,
                    'isDisable' => true,
                    'order' => 3

                ],
                [
                    'type' => 'string',
                    'name' => 'Должность руководителя организации',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'position',
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'group' => 'rq',
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
                    'group' => 'rq',
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
                    'group' => 'rq',
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
                    'group' => 'rq',
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
                    'group' => 'rq',
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
                    'includes' => ['org', 'org_state', 'ip', 'advokat'],
                    'group' => 'rq',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 0,

                ],

                [
                    'type' => 'string',
                    'name' => 'Адрес прописки',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'registredAdress',
                    'includes' => ['fiz'],
                    'group' => 'rq',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 1,

                ],
                [
                    'type' => 'string',
                    'name' => 'Фактический адрес',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'primaryAdresss',
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'group' => 'rq',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 2,

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
                    'order' => 0,
                ],
                [
                    'type' => 'string',
                    'name' => 'Адрес банка',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'bankAdress',
                    'includes' => ['org', 'org_state', 'ip', 'advokat'],
                    'group' => 'bank',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 1,
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
                    'order' => 2,
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
                    'order' => 3,
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
                    'order' => 4,
                ],

                [
                    'type' => 'text',
                    'name' => 'Прочие банковские реквизиты',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'bankOther',
                    'includes' => ['org', 'org_state', 'ip', 'advokat'],
                    'group' => 'bank',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 5,
                ],
            ]
        ];

        foreach ($bxRq as $bxRqFieldName => $value) {
            switch ($bxRqFieldName) {
                case 'NAME': //Название реквизита. Обязательное поле.

                    break;
                case 'RQ_NAME': //Ф.И.О.  физлица ип

                    for ($i = 0; $i < count($result['rq']); $i++) {
                        if ($result['rq'][$i]['code'] === 'fullname' || $result['rq'][$i]['code'] === 'personName') {
                            $result['rq'][$i]['value'] = $value;
                        }
                    }

                    // foreach ($result['rq'] as $rq) {

                    //     if ($rq['code'] === 'fullname') {
                    //         $rq['value'] = $value;
                    //     }

                    //     if ($rq['code'] === 'personName') {
                    //         $rq['value'] = $value;
                    //     }
                    // }
                    break;
                case 'RQ_COMPANY_NAME': //Сокращенное наименование организации.
                    // foreach ($result['rq'] as $rq) {
                    //     if ($rq['code'] === 'name') {
                    //         $rq['value'] = $value;
                    //     }
                    // }
                    for ($i = 0; $i < count($result['rq']); $i++) {
                        if ($result['rq'][$i]['code'] === 'name') {
                            $result['rq'][$i]['value'] = $value;
                        }
                    }
                    break;
                case 'RQ_COMPANY_FULL_NAME':
                    // Log::channel('telegram')->info('ONLINE TEST', [$bxRqFieldName => $value]);

                    // foreach ($result['rq'] as $rq) {
                    //     if ($rq['code'] === 'fullname') {
                    //         $rq['value'] = $value;
                    //         Log::channel('telegram')->info('ONLINE TEST', ['$result rq' => $rq]);
                    //     }
                    // }
                    // Log::channel('telegram')->info('ONLINE TEST', ['$result rq' => $result['rq']]);
                    for ($i = 0; $i < count($result['rq']); $i++) {
                        if ($result['rq'][$i]['code'] === 'fullname') {
                            $result['rq'][$i]['value'] = $value;
                        }
                    }
                    break;
                case 'RQ_DIRECTOR': //Ген. директор.

                    for ($i = 0; $i < count($result['rq']); $i++) {
                        if ($result['rq'][$i]['code'] === 'director') {
                            $result['rq'][$i]['value'] = $value;
                        }
                    }
                    break;
                case 'RQ_ACCOUNTANT': // Гл. бухгалтер.
                    for ($i = 0; $i < count($result['rq']); $i++) {
                        if ($result['rq'][$i]['code'] === 'accountant') {
                            $result['rq'][$i]['value'] = $value;
                        }
                    }

                    break;
                case 'RQ_EMAIL':

                    for ($i = 0; $i < count($result['rq']); $i++) {
                        if ($result['rq'][$i]['code'] === 'email') {
                            $result['rq'][$i]['value'] = $value;
                        }
                    }

                    break;
                case 'RQ_PHONE':

                    for ($i = 0; $i < count($result['rq']); $i++) {
                        if ($result['rq'][$i]['code'] === 'phone') {
                            $result['rq'][$i]['value'] = $value;
                        }
                    }

                    break;


                    //fiz lic
                case 'RQ_IDENT_DOC': //Вид документа.

                    for ($i = 0; $i < count($result['rq']); $i++) {
                        if ($result['rq'][$i]['code'] === 'document') {
                            $result['rq'][$i]['value'] = $value;
                        }
                    }

                    break;
                case 'RQ_IDENT_DOC_SER': //Серия.

                    for ($i = 0; $i < count($result['rq']); $i++) {
                        if ($result['rq'][$i]['code'] === 'docSer') {
                            $result['rq'][$i]['value'] = $value;
                        }
                    }
                    break;
                case 'RQ_IDENT_DOC_NUM': // Номер.

                    for ($i = 0; $i < count($result['rq']); $i++) {
                        if ($result['rq'][$i]['code'] === 'docNum') {
                            $result['rq'][$i]['value'] = $value;
                        }
                    }
                    break;
                case 'RQ_IDENT_DOC_DATE': //Дата выдачи.

                    for ($i = 0; $i < count($result['rq']); $i++) {
                        if ($result['rq'][$i]['code'] === 'docDate') {
                            $result['rq'][$i]['value'] = $value;
                        }
                    }
                    break;

                case 'RQ_IDENT_DOC_ISSUED_BY': //Кем выдан.

                    for ($i = 0; $i < count($result['rq']); $i++) {
                        if ($result['rq'][$i]['code'] === 'docIssuedBy') {
                            $result['rq'][$i]['value'] = $value;
                        }
                    }
                    break;

                case 'RQ_IDENT_DOC_DEP_CODE': //Код подразделения

                    for ($i = 0; $i < count($result['rq']); $i++) {
                        if ($result['rq'][$i]['code'] === 'docDepCode') {
                            $result['rq'][$i]['value'] = $value;
                        }
                    }
                    break;


                case 'RQ_INN':

                    for ($i = 0; $i < count($result['rq']); $i++) {
                        if ($result['rq'][$i]['code'] === 'inn') {
                            $result['rq'][$i]['value'] = $value;
                        }
                    }
                    break;

                case 'RQ_KPP':

                    for ($i = 0; $i < count($result['rq']); $i++) {
                        if ($result['rq'][$i]['code'] === 'kpp') {
                            $result['rq'][$i]['value'] = $value;
                        }
                    }
                    break;

                case 'RQ_OGRN':

                    for ($i = 0; $i < count($result['rq']); $i++) {
                        if ($result['rq'][$i]['code'] === 'ogrn') {
                            $result['rq'][$i]['value'] = $value;
                        }
                    }
                    break;


                case 'RQ_OGRNIP':


                    for ($i = 0; $i < count($result['rq']); $i++) {
                        if ($result['rq'][$i]['code'] === 'ogrnip') {
                            $result['rq'][$i]['value'] = $value;
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

                    break;
            }
        }

        if (!empty($bxAdressesRq) && is_array($bxAdressesRq)) {
            foreach ($bxAdressesRq as $bxAdressRq) {
                $isRegisrted = false;
                $isFizRegisrted = false;
                if ($bxAdressRq['TYPE_ID'] === 4) { // Адрес регистрации
                    $isRegisrted = true;

                    if (!empty($bxAdressRq['ADDRESS_1'])) {
                        $advalue = $this->getAddressString($bxAdressRq);

                        // foreach ($result['address'] as $resultAddress) {
                        //     if ($resultAddress['code'] == 'registredAdress' && $resultAddress['name'] ==  'Адрес прописки') {
                        //         $resultAddress['value'] = $advalue;
                        //     }
                        // }
                        for ($i = 0; $i < count($result['address']); $i++) {
                            if ($result['address'][$i]['code'] === 'registredAdress'  && $result['address'][$i]['name'] ==  'Адрес прописки') {
                                $result['address'][$i]['value'] = $advalue;
                            }
                        }
                    }
                } else  if ($bxAdressRq['TYPE_ID'] === 6) {  // Юридический адрес 
                    if (!empty($bxAdressRq['ADDRESS_1'])) {
                        $advalue = $this->getAddressString($bxAdressRq);

                        // foreach ($result['address'] as $resultAddress) {
                        //     if ($resultAddress['code'] == 'registredAdress') {
                        //         $resultAddress['value'] = $advalue;
                        //     }
                        // }

                        for ($i = 0; $i < count($result['address']); $i++) {
                            if ($result['address'][$i]['code'] === 'registredAdress') {
                                $result['address'][$i]['value'] = $advalue;
                            }
                        }
                    }
                } else {
                    if (!empty($bxAdressRq['ADDRESS_1'])) {
                        $advalue = $this->getAddressString($bxAdressRq);

                        // foreach ($result['address'] as $resultAddress) {
                        //     if ($resultAddress['code'] == 'primaryAdresss') {
                        //         $resultAddress['value'] = $advalue;
                        //     }
                        // }

                        for ($i = 0; $i < count($result['address']); $i++) {
                            if ($result['address'][$i]['code'] === 'primaryAdresss') {
                                $result['address'][$i]['value'] = $advalue;
                            }
                        }
                    }
                }
            }
        }

        foreach ($bxBankRq as $bxBankFieldName => $value) {
            switch ($bxBankFieldName) {
                case 'NAME': //Название реквизита. Обязательное поле.
                    # code...
                    break;
                case 'RQ_BANK_NAME': //Ф.И.О.  физлица ип
                    // foreach ($result['bank'] as $bankRQ) {
                    //     if ($bankRQ['code'] === 'bank') {
                    //         $bankRQ['value'] = $value;
                    //     }
                    // }
                    for ($i = 0; $i < count($result['bank']); $i++) {
                        if ($result['bank'][$i]['code'] === 'bank') {
                            $result['bank'][$i]['value'] = $value;
                        }
                    }

                    break;
                case 'RQ_BANK_ADDR': //Сокращенное наименование организации.
                    // foreach ($result['bank'] as $bankRQ) {
                    //     if ($bankRQ['code'] === 'bankAdress') {
                    //         $bankRQ['value'] = $value;
                    //     }
                    // }
                    for ($i = 0; $i < count($result['bank']); $i++) {
                        if ($result['bank'][$i]['code'] === 'bankAdress') {
                            $result['bank'][$i]['value'] = $value;
                        }
                    }


                    break;
                case 'RQ_BIK':
                    // foreach ($result['bank'] as $bankRQ) {
                    //     if ($bankRQ['code'] === 'bik') {
                    //         $bankRQ['value'] = $value;
                    //     }
                    // }
                    for ($i = 0; $i < count($result['bank']); $i++) {
                        if ($result['bank'][$i]['code'] === 'bik') {
                            $result['bank'][$i]['value'] = $value;
                        }
                    }
                    break;
                case 'RQ_COR_ACC_NUM': //Ген. директор.
                    // foreach ($result['bank'] as $bankRQ) {
                    //     if ($bankRQ['code'] === 'ks') {
                    //         $bankRQ['value'] = $value;
                    //     }
                    // }
                    for ($i = 0; $i < count($result['bank']); $i++) {
                        if ($result['bank'][$i]['code'] === 'ks') {
                            $result['bank'][$i]['value'] = $value;
                        }
                    }
                    break;
                case 'RQ_IBAN': // Гл. бухгалтер.
                    # code...
                    break;
                case 'RQ_ACC_NUM':
                    // foreach ($result['bank'] as $bankRQ) {
                    //     if ($bankRQ['code'] === 'rs') {
                    //         $bankRQ['value'] = $value;
                    //     }
                    // }
                    for ($i = 0; $i < count($result['bank']); $i++) {
                        if ($result['bank'][$i]['code'] === 'rs') {
                            $result['bank'][$i]['value'] = $value;
                        }
                    }
                    break;
                case 'COMMENTS':
                    // foreach ($result['bank'] as $bankRQ) {
                    //     if ($bankRQ['code'] === 'bankOther') {
                    //         $bankRQ['value'] = $value;
                    //     }
                    // }
                    for ($i = 0; $i < count($result['bank']); $i++) {
                        if ($result['bank'][$i]['code'] === 'bankOther') {
                            $result['bank'][$i]['value'] = $value;
                        }
                    }
                    break;
                default:
                    // foreach ($result['bank'] as $bankRQ) {
                    //     if ($bankRQ['code'] === 'bankOther') {
                    //         $rq['value'] = $rq['value'] . ' ' . $value;
                    //     }
                    // }
            }
        }
        return $result;
    }
    protected function getContractGeneralForm($arows, $contractQuantity)
    {


        $prepaymentQuantity = $contractQuantity;
        $prepaymentSum = 0;
        $monthSum = 0;
        $contractsum = 0;

        foreach ($arows as $row) {
            if (!empty($row['price'])) {
                $monthSum = (float)$monthSum  + (float)$row['price']['month'];
                $prepaymentSum = (float)$prepaymentSum  + (float)$row['price']['sum'];
                $prepaymentQuantity = (float)$contractQuantity + (float)$row['price']['quantity'];
            }
        }
        $contractsum = $monthSum * 12;
      
      
        return [
            [
                'type' => 'date',
                'name' => 'Договор от',
                'value' => '',
                'isRequired' => true,
                'code' => 'contract_create_date',
                'group' => 'contract',
                'isActive' => true,
                'isDisable' => false,
                'order' => 0,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],


            ],
            [
                'type' => 'date',
                'name' => 'Действие договора с',
                'value' => '',
                'isRequired' => true,
                'code' => 'contract_start',
                'group' => 'contract',
                'isActive' => true,
                'isDisable' => false,
                'order' => 1,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],


            ],
            [
                'type' => 'date',
                'name' => 'Действие договора по',
                'value' => '',
                'isRequired' => true,
                'code' => 'contract_finish',
                'group' => 'contract',
                'isActive' => true,
                'isDisable' => false,
                'order' => 2,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],


            ],
            [
                'type' => 'date',
                'name' => 'Предоплата с',
                'value' => '',
                'isRequired' => true,
                'code' => 'prepayment_start',
                'group' => 'contract',
                'isActive' => true,
                'isDisable' => false,
                'order' => 3,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],


            ],
            [
                'type' => 'date',
                'name' => 'Предоплата по',
                'value' => '',
                'isRequired' => true,
                'code' => 'prepayment_finish',
                'group' => 'contract',
                'isActive' => true,
                'isDisable' => false,
                'order' => 4,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],


            ],
            [
                'type' => 'date',
                'name' => 'Период в подарок с',
                'value' => '',
                'isRequired' => true,
                'code' => 'present_start',
                'group' => 'contract',
                'isActive' => true,
                'isDisable' => false,
                'order' => 3,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],


            ],
            [
                'type' => 'date',
                'name' => 'Период в подарок по',
                'value' => '',
                'isRequired' => true,
                'code' => 'present_finish',
                'group' => 'contract',
                'isActive' => true,
                'isDisable' => false,
                'order' => 4,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],


            ],
            [
                'type' => 'string',
                'name' => 'Сумма по договору',
                'value' => $contractsum,
                'isRequired' => true,
                'code' => 'period_sum',
                'group' => 'contract',
                'isActive' => true,
                'isDisable' => true,
                'order' => 5,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],


            ],
            [
                'type' => 'string',
                'name' => 'Сумма предоплаты',
                'value' =>  $prepaymentSum,
                'isRequired' => true,
                'code' => 'prepayment_sum',
                'group' => 'contract',
                'isActive' => true,
                'isDisable' => true,
                'order' => 5,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],


            ],
            [
                'type' => 'string',
                'name' => 'Сумма в месяц',
                'value' => $monthSum,
                'isRequired' => true,
                'code' => 'month_sum',
                'group' => 'contract',
                'isActive' => true,
                'isDisable' => false,
                'order' => 6,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],


            ],
        ];
    }


    protected function getSpecification($products, $contract)
    {


        // $prepaymentQuantity = $contractQuantity;
        $prepaymentSum = 0;
        $monthSum = 0;
        $contractsum = 0;

        // foreach ($arows as $row) {
        //     if (!empty($row['price'])) {
        //         $monthSum = (float)$monthSum  + (float)$row['price']['month'];
        //         $prepaymentSum = (float)$prepaymentSum  + (float)$row['price']['sum'];
        //         $prepaymentQuantity = (float)$contractQuantity + (float)$row['price']['quantity'];
        //     }
        // }
        // $contractsum = $monthSum * 12;
      
      
        return [
            [
                'type' => 'date',
                'name' => 'Договор от',
                'value' => '',
                'isRequired' => true,
                'code' => 'contract_create_date',
                'group' => 'contract',
                'isActive' => true,
                'isDisable' => false,
                'order' => 0,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],


            ],
            [
                'type' => 'date',
                'name' => 'Действие договора с',
                'value' => '',
                'isRequired' => true,
                'code' => 'contract_start',
                'group' => 'contract',
                'isActive' => true,
                'isDisable' => false,
                'order' => 1,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],


            ],
            [
                'type' => 'date',
                'name' => 'Действие договора по',
                'value' => '',
                'isRequired' => true,
                'code' => 'contract_finish',
                'group' => 'contract',
                'isActive' => true,
                'isDisable' => false,
                'order' => 2,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],


            ],
            [
                'type' => 'date',
                'name' => 'Предоплата с',
                'value' => '',
                'isRequired' => true,
                'code' => 'prepayment_start',
                'group' => 'contract',
                'isActive' => true,
                'isDisable' => false,
                'order' => 3,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],


            ],
            [
                'type' => 'date',
                'name' => 'Предоплата по',
                'value' => '',
                'isRequired' => true,
                'code' => 'prepayment_finish',
                'group' => 'contract',
                'isActive' => true,
                'isDisable' => false,
                'order' => 4,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],


            ],
            [
                'type' => 'date',
                'name' => 'Период в подарок с',
                'value' => '',
                'isRequired' => true,
                'code' => 'present_start',
                'group' => 'contract',
                'isActive' => true,
                'isDisable' => false,
                'order' => 3,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],


            ],
            [
                'type' => 'date',
                'name' => 'Период в подарок по',
                'value' => '',
                'isRequired' => true,
                'code' => 'present_finish',
                'group' => 'contract',
                'isActive' => true,
                'isDisable' => false,
                'order' => 4,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],


            ],
            [
                'type' => 'string',
                'name' => 'Сумма по договору',
                'value' => $contractsum,
                'isRequired' => true,
                'code' => 'period_sum',
                'group' => 'contract',
                'isActive' => true,
                'isDisable' => true,
                'order' => 5,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],


            ],
            [
                'type' => 'string',
                'name' => 'Сумма предоплаты',
                'value' =>  $prepaymentSum,
                'isRequired' => true,
                'code' => 'prepayment_sum',
                'group' => 'contract',
                'isActive' => true,
                'isDisable' => true,
                'order' => 5,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],


            ],
            [
                'type' => 'string',
                'name' => 'Сумма в месяц',
                'value' => $monthSum,
                'isRequired' => true,
                'code' => 'month_sum',
                'group' => 'contract',
                'isActive' => true,
                'isDisable' => false,
                'order' => 6,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],


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

    protected function getAddressString($bxAdressRq)
    {
        $advalue = '';

        if (!empty($bxAdressRq['POSTAL_CODE'])) {
            $advalue = $advalue . $bxAdressRq['POSTAL_CODE'] . ', ';
        }

        if (!empty($bxAdressRq['COUNTRY'])) {
            $advalue = $advalue . $bxAdressRq['COUNTRY'] . ', ';
        }

        if (!empty($bxAdressRq['PROVINCE'])) {
            $advalue = $advalue . $bxAdressRq['PROVINCE'] . ', ';
        }

        if (!empty($bxAdressRq['REGION'])) {
            $advalue = $advalue . $bxAdressRq['REGION'] . ', ';
        }
        if (!empty($bxAdressRq['CITY'])) {
            $advalue = $advalue . $bxAdressRq['CITY'] . ', ';
        }
        if (!empty($bxAdressRq['ADDRESS_1'])) {
            $advalue = $advalue . $bxAdressRq['ADDRESS_1'] . ', ';
        }
        if (!empty($bxAdressRq['ADDRESS_2'])) {
            $advalue = $advalue . $bxAdressRq['ADDRESS_2'] . ', ';
        }

        return $advalue;
    }
}
