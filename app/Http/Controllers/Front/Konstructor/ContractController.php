<?php

namespace App\Http\Controllers\Front\Konstructor;

use App\Http\Controllers\APIController;
use App\Http\Controllers\BitrixController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CounterController;
use App\Http\Resources\PortalContractResource;
use App\Models\Bitrixfield;
use App\Models\BitrixfieldItem;
use App\Models\Portal;
use App\Models\PortalContract;
use App\Services\BitrixDealDocumentContractService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\TemplateProcessor;

class ContractController extends Controller
{

    public function frontInit(Request $request) //by id
    {
        $data = $request->all();
        $domain = $data['domain'];
        $companyId = $data['companyId'];
        $contractType = $data['contractType']; //service | product

        $contract = $data['contract'];
        $generalContractModel = $contract['contract'];
        $contractQuantity = $generalContractModel['coefficient'];

        $productSet = $data['productSet'];
        $products = $data['products'];
        $contractProductName = $generalContractModel['productName'];
        $arows = $data['arows'];


        $currentComplect = $data['complect']; //lt  //ltInPacket
        $consaltingProduct = $data['consalting']['product'];
        $lt = $data['legalTech'];
        $starProduct = $data['star']['product'];

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
                'contract' => $this->getContractGeneralForm($arows, $contractQuantity),
                'specification' => $this->getSpecification(
                    $currentComplect,
                    $products,
                    $consaltingProduct,
                    $lt,
                    $starProduct,
                    $contractType, //service | product
                    $contract,

                    $arows,
                    $contractQuantity
                ),
                'clientType' =>  [
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
                        // [
                        //     'id' => 3,
                        //     'code' => 'advokat',
                        //     'name' => 'Адвокат',
                        //     'title' => 'Адвокат'
                        // ],
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

                'currentComplect' => $currentComplect,
                'products' => $products,
                'consaltingProduct' => $consaltingProduct,
                'lt' => $lt,
                'starProduct' => $starProduct,
                'contractType' => $contractType,


















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
            $clientRq = null;
            $clientRqBank = null;
            $clientRqAddress = null;
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
            }

            $client = $this->getClientRqForm($clientRq, $clientRqAddress, $clientRqBank, $contractType);
            $result['client'] = $client;
            return APIController::getSuccess(
                [
                    'init' => $result,
                    // 'addressresponse' => $result['client']['address'],
                    // 'clientRq' => $clientRq,
                    // 'clientRqBank' => $clientRqBank,
                    // 'clientRqAddress' => $clientRqAddress,
                ]
            );
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                ['companyId' => $companyId, 'domain' => $domain, 'products' => $products]
            );
        }
    }

    public function getContractDocument(Request $request)
    {
        $contractLink = '';
        $data = $request->all();
        $domain = $data['domain'];
        $companyId = $data['companyId'];
        $contractType = $data['contractType'];

        $contract = $data['contract'];
        $generalContractModel = $contract['contract'];
        $contractQuantity = $generalContractModel['coefficient'];

        $productSet = $data['productSet']; //все продукты rows из general в виде исходного стэйт объекта

        $products = $data['products'];  //productsFromRows  объекты продуктов с полями для договоров полученные из rows 
        $contractProductName = $generalContractModel['productName']; // приставка к имени продукта из current contract
        $isProduct = $contractType !== 'service';
        $contractCoefficient = $contract['prepayment'];




        $arows = $data['arows']; //все продукты rows из general в виде массива
        $total = $productSet['total'][0];




        $contractGeneralFields = $data['contractBaseState']['items']; //fields array

        $contractClientState = $data['contractClientState']['client'];
        $clientRq = $contractClientState['rqs']['rq'];                //fields array
        $clientRqBank = $contractClientState['rqs']['bank'];

        $providerState = $data['contractProviderState'];

        $providerRq = $providerState['current']['rq'];
        $etalonPortal = Portal::where('domain', 'april-dev.bitrix24.ru')->first();
        $template = $etalonPortal->templates()
            ->where('type', 'contract')
            ->where('code', 'proxima')
            ->first();
        $templateField = $template->fields()
            ->where('type', 'template')
            ->where('code', 'proxima')
            ->first();

        $templatePath = $templateField['value'];
        if (substr($templatePath, 0, 8) === '/storage') {
            $relativePath = substr($templatePath, 8); // Обрезаем первые 8 символов
        }

        // Проверяем, существует ли файл
        if (Storage::disk('public')->exists($relativePath)) {
            // Строим полный абсолютный путь
            $fullPath = storage_path('app/public') . '/' . $relativePath;

            // Теперь $fullPath содержит полный путь к файлу
            $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($fullPath);

            $templateData = $this->getDocumentData(

                $contractType,

                // header
                $clientRq, //header and rq and general
                $providerRq,

                //  //specification 2 prices
                $arows,
                $total,
                $contractProductName,
                $isProduct,
                $contractCoefficient,

                //specification 1 tech fields
                $products,
                $contractGeneralFields,
                // $clientRq,
                $clientRqBank,

                // general dates and sums at body

            );
            //templatecontent


            $documentNumber = CounterController::getCount($providerRq['id'], 'offer');


            $templateProcessor->setValue('header', $templateData['header']);
            $templateProcessor->cloneRowAndSetValues('productNumber', $templateData['products']);



            // Дальнейшие действия с документом...
            $resultPath = storage_path('app/public/clients/' . $data['domain'] . '/documents/contracts/' . $data['userId']);

            if (!file_exists($resultPath)) {
                // Log::channel('telegram')->error('APRIL_ONLINE', [
                //     'resultPath' => $resultPath
                // ]);

                mkdir($resultPath, 0775, true); // Создать каталог с правами доступа
            }
            if (!is_writable($resultPath)) {
                Log::channel('telegram')->error('APRIL_ONLINE', [
                    '!is_writable resultPath' => $resultPath
                ]);
                throw new \Exception("Невозможно записать в каталог: $resultPath");
            }
            $resultFileName = 'contract_test.docx';
            $templateProcessor->saveAs($resultPath . '/' . $resultFileName);
            $contractLink = asset('storage/clients/' . $domain . '/documents/contracts/' . $data['userId'] . '/' . $resultFileName);
        } else {
            return APIController::getError(
                'шаблон не найден',
                ['contractData' => $data, 'link' => $relativePath, 'template' => $template, 'templateField' => $templateField]
            );
        }       // // Создаем экземпляр обработчика шаблона
        // $templateProcessor = new TemplateProcessor($templatePath);

        // // Замена заполнителей простыми значениями
        // $templateProcessor->setValue('name', 'John Doe');
        // $templateProcessor->setValue('address', '123 Main Street');

        // // Предположим, что у нас есть массив данных для таблицы
        // $products = [
        //     ['name' => 'Product A', 'quantity' => 2],
        //     ['name' => 'Product B', 'quantity' => 5]
        // ];

        // // Добавление строк в таблицу
        // $templateProcessor->cloneRowAndSetValues('product_name', $products);

        // // Сохраняем измененный документ
        // $savePath = 'path/to/output.docx';
        // $templateProcessor->saveAs($savePath);

        return APIController::getSuccess(
            ['contractData' => $data, 'link' => $contractLink, 'template' => $template, 'templateField' => $templateField]
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
                    'isActive' => false,
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
                    'type' => 'text',
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
                    'type' => 'text',
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
                    'type' => 'text',
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
        if (!empty($bxRq)) {

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
                                array_push($result['rq'], $result['address'][$i]);
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
                                array_push($result['rq'], $result['address'][$i]);
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
                                array_push($result['rq'], $result['address'][$i]);
                            }
                        }
                    }
                }
            }
        }
        if (!empty($bxBankRq)) {

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
        }
        return ['rq' => $result['rq'], 'bank' => $result['bank']];
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
                $prepaymentQuantity = (float)$contractQuantity * (float)$row['price']['quantity'];
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
                'name' => 'Внести предоплату не позднее',
                'value' => '',
                'isRequired' => true,
                'code' => 'prepayment_deadline',
                'group' => 'contract',
                'isActive' => true,
                'isDisable' => false,
                'order' => 3,
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


    protected function getSpecification(
        $currentComplect,
        $products, //garant product or products from general garant only
        $consaltingProduct,
        $lt,
        $starProduct,
        $contractType, //service | product
        $contract,

        $arows,
        $contractQuantity
    ) {

        $productType = [
            'abs' => false,
            'complectName' => 'Юрист',
            'complectNumber' => 2,
            'complectType' => 'prof',
            'contract' => [
                'id' => 3,
                'contract' => ['details' => 'Детали контракта'],
                'code' => 'internet',
                'shortName' => 'internet',
                'number' => 0
            ],
            'contractCoefficient' => 1,
            'contractConsaltingComment' => '',
            'contractConsaltingProp' => '',
            'contractName' => 'Internet',
            'contractNumber' => 0,
            'contractShortName' => 'internet',
            'contractSupplyName' => 'В электронном виде по каналам связи посредством телекоммуникационной сети Интернет Многопользовательская Интернет-версия 1',
            'contractSupplyProp1' => '',
            'contractSupplyProp2' => '',
            'contractSupplyPropComment' => 'Для работы с комплектом Справочника в электронном виде по каналам связи посредством телекоммуникационной сети Исполнитель предоставляет Заказчику в электронном виде на адрес электронной почты, указанный Заказчиком в настоящем Приложении, информацию об административной учетной записи, с помощью которой Заказчиком заводятся логины и пароли Пользователей.',
            'contractSupplyPropEmail' => 'Адрес электронной почты Заказчика, на который Исполнитель присылает информацию об административной учетной записи.',
            'contractSupplyPropLoginsQuantity' => 'Неограниченно с возможностью одновременной работы одного Пользователя',
            'contractSupplyPropSuppliesQuantity' => 1,
            'discount' => 1,
            'measureCode' => 'month',
            'measureFullName' => 'Месяц',
            'measureId' => 11,
            'measureName' => 'mec.',
            'measureNumber' => 2,
            'mskPrice' => false,
            'name' => 'Гарант-Юрист',
            'number' => 851,
            'prepayment' => 1,
            'price' => 8008,
            'productId' => null,
            'quantityForKp' => 'Интернет версия на 1 одновременный доступ к системе',
            'regionsPrice' => false,
            'supply' => [
                'contractPropSuppliesQuantity' => 1,
                'lcontractProp2' => '',
                'lcontractName' => 'Многопользовательская Интернет-версия 1',
                'lcontractPropEmail' => 'Адрес электронной почты Лицензиата, на который Лицензиар присылает информацию.',
                'type' => 'internet'
            ],
            'supplyName' => 'Интернет 1 ОД',
            'supplyNumber' => 1,
            'supplyType' => 'internet',
            'totalPrice' => 8008,
            'type' => 'garant',
            'withAbs' => false,
            'withConsalting' => false,
            'withPrice' => false
        ];

        $prepaymentQuantity = $contractQuantity;
        $prepaymentSum = 0;
        $monthSum = 0;
        $contractsum = 0;
        $product = $products[0];
        $contractSupplyName = $product['contractSupplyName'];
        $contractSupplyPropComment = $product['contractSupplyPropComment'];
        $contractSupplyPropEmail = $product['contractSupplyPropEmail'];
        $consalting =  $contractConsaltingComment = $product['contractConsaltingComment'];

        foreach ($arows as $row) {
            if (!empty($row['price'])) {
                $monthSum = (float)$monthSum  + (float)$row['price']['month'];
                $prepaymentSum = (float)$prepaymentSum  + (float)$row['price']['sum'];
                $prepaymentQuantity = (float)$contractQuantity * (float)$row['price']['quantity'];
            }
        }
        $contractsum = $monthSum * 12;
        $products_names = 'Гарант-' . $product['complectName'] . ' ' . $product['supply']['name'];
        $consalting = '';
        $consaltingcomment = '';
        if ($consaltingProduct) {
            $consalting = $consaltingProduct['contractConsaltingProp'];
            $consaltingcomment = $consaltingProduct['contractConsaltingComment'];
        }
        $ltProduct = $lt['product'];

        $freeLtPack = '';
        $freeLtBlocks = '';
        $ltPack = '';
        $ltBlocks = '';



        if (!empty($currentComplect['lt'])) {
            $packWeight = count($currentComplect['lt']);
            $pack = $lt['packages'][$packWeight];
            // if (!empty($pack)) {
            $freeLtPack =  $pack['fullName'];
            // }

            // foreach ($currentComplect['lt'] as $ltIndex) {
            //     $freeLtBlocks = $freeLtBlocks . ' ' . $lt['value'][$ltIndex]['name'];
            // }
            foreach ($lt['value'] as $ltservice) {
                if (in_array($ltservice['number'], $currentComplect['lt'])) {
                    $freeLtBlocks .=  ' ' . $ltservice['name'] . "\n";
                }
            }
        }
        if (!empty($currentComplect['ltInPacket'])) {
            $packWeight = count($currentComplect['ltInPacket']);

            $pack = $lt['packages'][$packWeight];
            // if (!empty($pack)) {
            $ltPack =  $pack['fullName'];
            // }

            foreach ($lt['value'] as $ltservice) {
                if (in_array($ltservice['number'], $currentComplect['ltInPacket'])) {
                    $ltBlocks .=  '' . $ltservice['name'] . "\n";;
                }
            }

            // foreach ($currentComplect['ltInPacket'] as $ltIndex) {
            //     $freeLtBlocks .= ' ' . $lt['value'][$ltIndex]['name'];
            // }
        }


        if (!empty($contract))

            return [
                [
                    'type' => 'string',
                    'name' => 'Наименование  комплекта частей  Справочника',
                    'value' => $products_names,
                    'isRequired' => true,
                    'code' => 'contract_spec_products_names',
                    'group' => 'specification',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 0,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'supplies' => ['internet', 'proxima'],
                    'contractType' => ['service', 'lic', 'abon', 'key']


                ],
                [
                    'type' => 'string',
                    'name' => 'Комментарий к наименованию',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'contract_spec_products_names_comment',
                    'group' => 'specification',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 1,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'supplies' => ['internet', 'proxima'],
                    'contractType' => ['service', 'lic', 'abon', 'key']


                ],
                [
                    'type' => 'string',
                    'name' => 'ПК/ГЛ',
                    'value' => $consalting,
                    'isRequired' => true,
                    'code' => 'specification_pk',
                    'group' => 'specification',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 2,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'supplies' => ['internet', 'proxima'],
                    'contractType' => ['service', 'lic', 'abon', 'key']


                ],
                [
                    'type' => 'string',
                    'name' => 'Комментарий к ПК/ГЛ',
                    'value' => $consaltingcomment,
                    'isRequired' => true,
                    'code' => 'specification_pk_comment',
                    'group' => 'specification',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 3,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'supplies' => ['internet', 'proxima'],
                    'contractType' => ['service', 'lic', 'abon', 'key']


                ],
                [
                    'type' => 'text',
                    'name' => 'Большие информацмонные блоки',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'specification_ibig',
                    'group' => 'specification',
                    'isActive' => false,
                    'isDisable' => false,
                    'order' => 4,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'supplies' => ['internet', 'proxima'],
                    'contractType' => ['service', 'lic', 'abon', 'key']


                ],
                [
                    'type' => 'text',
                    'name' => 'Малые информацмонные блоки',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'specification_ismall',
                    'group' => 'specification',
                    'isActive' => false,
                    'isDisable' => false,
                    'order' => 5,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'supplies' => ['internet', 'proxima'],
                    'contractType' => ['service', 'lic', 'abon', 'key']


                ],
                [
                    'type' => 'text',
                    'name' => 'Бесплатные информационные блоки',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'specification_ifree',
                    'group' => 'specification',
                    'isActive' => false,
                    'isDisable' => false,
                    'order' => 6,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'supplies' => ['internet', 'proxima'],
                    'contractType' => ['service', 'lic', 'abon', 'key']


                ],
                [
                    'type' => 'string',
                    'name' => 'Legal Tech в комплекте',
                    'value' => $freeLtPack,
                    'isRequired' => true,
                    'code' => 'specification_lt_free',
                    'group' => 'specification',
                    'isActive' => true,
                    'isDisable' => true,
                    'order' => 7,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'supplies' => ['internet', 'proxima'],
                    'contractType' => ['service', 'lic', 'abon', 'key']


                ],
                [
                    'type' => 'text',
                    'name' => 'Legal Tech в комплекте',
                    'value' => $freeLtBlocks,
                    'isRequired' => true,
                    'code' => 'specification_lt_free_services',
                    'group' => 'specification',
                    'isActive' => true,
                    'isDisable' => true,
                    'order' => 8,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'supplies' => ['internet', 'proxima'],
                    'contractType' => ['service', 'lic', 'abon', 'key']


                ],
                [
                    'type' => 'string',
                    'name' => 'Пакет Legal Tech',
                    'value' => $ltPack,
                    'isRequired' => true,
                    'code' => 'specification_lt_packet',
                    'group' => 'specification',
                    'isActive' => true,
                    'isDisable' => true,
                    'order' => 9,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'supplies' => ['internet', 'proxima'],
                    'contractType' => ['service', 'lic', 'abon', 'key']


                ],
                [
                    'type' => 'text',
                    'name' => 'Пакет Legal Tech',
                    'value' => $ltBlocks,
                    'isRequired' => true,
                    'code' => 'specification_lt_services',
                    'group' => 'specification',
                    'isActive' => true,
                    'isDisable' => true,
                    'order' => 10,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'supplies' => ['internet', 'proxima'],
                    'contractType' => ['service', 'lic', 'abon', 'key']


                ],
                [
                    'type' => 'text',
                    'name' => 'Другие сервисы',
                    'value' =>  '',
                    'isRequired' => true,
                    'code' => 'specification_services',
                    'group' => 'specification',
                    'isActive' => false,
                    'isDisable' => true,
                    'order' => 11,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'supplies' => ['internet', 'proxima'],
                    'contractType' => ['service', 'lic', 'abon', 'key']


                ],
                [
                    'type' => 'string',
                    'name' => 'Вид Размещения',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'specification_supply',
                    'group' => 'specification',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 12,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'supplies' => ['internet', 'proxima'],
                    'contractType' => ['service', 'lic', 'abon', 'key']


                ],
                [
                    'type' => 'string',
                    'name' => 'Примечание Вид Размещения',
                    'value' =>  '',
                    'isRequired' => true,
                    'code' => 'specification_supply_comment',
                    'group' => 'specification',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 13,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'supplies' => ['internet', 'proxima'],
                    'contractType' => ['service', 'lic', 'abon', 'key']


                ],
                [
                    'type' => 'string',
                    'name' => 'Носители, используемые при предоставлении услуг',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'specification_distributive',
                    'group' => 'specification',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 14,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'supplies' => ['internet', 'proxima'],
                    'contractType' => ['service', 'lic', 'abon', 'key']


                ],
                [
                    'type' => 'string',
                    'name' => 'Примечание Носители',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'specification_distributive_comment',
                    'group' => 'specification',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 15,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'supplies' => ['internet', 'proxima'],


                ],
                [
                    'type' => 'string',
                    'name' => 'Носители дистрибутивов предоставляются следующим способом',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'specification_dway',
                    'group' => 'specification',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 16,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'contractType' => ['service', 'lic', 'abon', 'key'],
                    'supplies' => ['internet', 'proxima'],


                ],
                [
                    'type' => 'string',
                    'name' => 'Примечание к способу',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'specification_dway_comment',
                    'group' => 'specification',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 17,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'contractType' => ['service', 'lic', 'abon', 'key'],
                    'supplies' => ['internet', 'proxima'],


                ],
                [
                    'type' => 'string',
                    'name' => 'Периодичность предоставления услуг',
                    'value' => '1 неделя',
                    'isRequired' => true,
                    'code' => 'specification_service_period',
                    'group' => 'specification',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 18,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'contractType' => ['service'],
                    'supplies' => ['internet', 'proxima'],

                ],
                [
                    'type' => 'string',
                    'name' => 'Длительность работы ключа',
                    'value' => $prepaymentQuantity . 'мес.',
                    'isRequired' => true,
                    'code' => 'specification_key_period',
                    'group' => 'specification',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 19,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'contractType' => ['lic', 'abon', 'key'],
                    'supplies' => ['internet', 'proxima'],


                ],
                [
                    'type' => 'string',
                    'name' => 'Email для интернет-версии',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'specification_email',
                    'group' => 'specification',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 20,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'contractType' => ['service'],
                    'supplies' => ['internet', 'proxima'],

                ],
                [
                    'type' => 'string',
                    'name' => 'Длительность работы ключа',
                    'value' => $contractSupplyPropEmail,
                    'isRequired' => true,
                    'code' => 'specification_email_comment',
                    'group' => 'specification',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 21,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'contractType' => ['lic', 'abon', 'key'],
                    'supplies' => ['internet', 'proxima'],


                ],



            ];
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



    //contract document data
    protected function getDocumentData(
        $contractType,

        // header
        $clientRq, //header and rq and general
        $providerRq,

        //  //specification 2 prices
        $arows,
        $total,
        $contractProductName,
        $isProduct,
        $contractCoefficient,

        //specification 1 tech fields
        $products,
        $contractGeneralFields,
        // $clientRq,
        $clientRqBank,

    ) {

        $products = $this->getProducts(
            $arows,
            $contractProductName,
            $isProduct,
            $contractCoefficient
        );
        $header = $this->getContractHeaderText(
            $contractType,
            $clientRq,
            $providerRq,
            // $clientCompanyFullName,
            // $clientCompanyDirectorNameCase,   //директор | ип | ''
            // $clientCompanyDirectorPositionCase,
            // $clientCompanyBased,
            // $providerCompanyFullName,
            // $providerCompanyDirectorNameCase,
            // $providerCompanyDirectorPositionCase,
            // $providerCompanyBased,
        );
        // $specification = 

        return [
            'products' =>  $products,
            'header' =>  $header,
            // 'documentNumber' =>  $documentNumber,
            // 'contractSum' =>  $contractSum,
        ];
    }

    protected function getContractHeaderText(
        $contractType,
        // $clientCompanyFullName,
        // $clientCompanyDirectorNameCase,   //директор | ип | ''
        // $clientCompanyDirectorPositionCase,
        // $clientCompanyBased,
        // $providerCompanyFullName,
        // $providerCompanyDirectorNameCase,
        // $providerCompanyDirectorPositionCase,
        // $providerCompanyBased,
        $clientRq,
        $providerRq,
    ) {

        $clientRole = 'Заказчик';
        $providerRole = 'Исполнитель';
        switch ($contractType) {
            case 'abon':
            case 'key':
                $clientRole = 'Покупатель';
                $providerRole = 'Постващик';
                break;
            case 'lic':
                $clientRole = 'Лицензиат';
                $providerRole = 'Лицензиар';
                break;
            default:
                $clientRole = 'Заказчик';
                $providerRole = 'Исполнитель';
                # code...
                break;
        }
        $providerCompanyFullName = $providerRq['fullname'];
        $providerCompanyDirectorNameCase = $providerRq['director'];
        $providerCompanyDirectorPositionCase = $providerRq['position'];
        $providerCompanyBased = 'Устава';

        $clientCompanyFullName = '';
        $clientCompanyDirectorNameCase = '';
        $clientCompanyDirectorPositionCase = '';
        $clientCompanyBased = '';

        foreach ($clientRq as $rqItem) {
            if ($rqItem['code'] === 'fullname') {
                $clientCompanyFullName = $rqItem['value'];
            } else  if ($rqItem['code'] === 'position_case') {
                $clientCompanyDirectorPositionCase = $rqItem['value'];
            } else  if ($rqItem['code'] === 'director_case') {
                $clientCompanyDirectorNameCase = $rqItem['value'];
            } else  if ($rqItem['code'] === 'based') {
                $clientCompanyBased = $rqItem['value'];
            }
        }

        $headerText = $providerCompanyFullName . ' , официальный партнер компании "Гарант", 
        именуемый в дальнейшем "' . $providerRole . '", в лице ' . $providerCompanyDirectorPositionCase . ' ' . $providerCompanyDirectorNameCase . ', действующего(-ей) на основании'
            . $providerCompanyBased . ' с одной стороны и ' . $clientCompanyFullName . ', 
        именуемое(-ый) в дальнейшем "' . $clientRole . '", в лице ' . $clientCompanyDirectorPositionCase . ' ' . $clientCompanyDirectorNameCase . ', действующего(-ей) на основании'
            . $clientCompanyBased . ' с другой стороны, заключили настоящий Договор о нижеследующем:';


        return $headerText;
    }

    protected function getDocumentNumber()
    {
    }
    protected function getProducts($arows, $contractName, $isProduct, $contractCoefficient)
    {
        $contractFullName = $contractName;
        if ($isProduct) {
            $contractFullName = $contractFullName . ' длительность ' . $contractCoefficient . ' мес. ';
        }

        $products = [];
        foreach ($arows as $key => $row) {
            $product = [
                'productNumber' => $key + 1,
                'productName' => $contractFullName . '(' . $row['name'] . ')',
                'productQuantity' => $row['price']['quantity'],
                'productMeasure' => $row['price']['measure']['name'],
                'productPrice' => $row['price']['current'],
                'productSum' => $row['price']['sum'],
            ];
            array_push($products, $product);
        }

        return $products;
    }
}
