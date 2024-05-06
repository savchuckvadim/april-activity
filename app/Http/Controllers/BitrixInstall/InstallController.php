<?php

namespace App\Http\Controllers\BitrixInstall;

use App\Http\Controllers\APIController;
use App\Http\Controllers\BitrixController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\PortalController;
use FontLib\Table\Type\name;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InstallController extends Controller
{
    public static function installSmart(
        // $domain,
        // $smarts
    )
    {
        // $domain = 'gsr.bitrix24.ru';
        $domain = 'april-dev.bitrix24.ru';

        $method = '/crm.deal.userfield.add';
        $hook = BitrixController::getHook($domain);
        Log::channel('telegram')->info('APRIL_ONLINE TEST', ['hook' => ['hook' => $hook]]);

        $url = $hook . $method;
        //1) создает смарт процесс и сам задает  "entityTypeId" => 134,

        //3) записывает стадии и направления ввиде одного объекта json связь portal-smart

        // $initialData = Http::get(
        //     ''
        // );

      
        try {
            $portal = PortalController::innerGetPortal($domain);
            Log::channel('telegram')->info('APRIL_ONLINE TEST', ['INSTALL' => ['portal' => $portal]]);
            $newSmart = null;
            $categories = null;
            $token = 'AKfycbwj00QG9Bv1J3H5r3BJuYmqVy9hhIxdfUPGQVqBhi2zhZnvHVxjlzI6g19d2WAC1unZ';
            $url = 'https://script.google.com/macros/s/' . $token . '/exec';
            $response = Http::get($url);

            if ($response->successful()) {
                $googleData = $response->json();
                Log::channel('telegram')->error("googleData", [
                    'googleData' => $googleData,

                ]);
            } else {
                Log::channel('telegram')->error("Failed to retrieve data from Google Sheets", [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return response(['resultCode' => 1, 'message' => 'Error retrieving data'], 500);
            }

            $smarts = null;


            $webhookRestKey = $portal['portal']['C_REST_WEB_HOOK_URL'];
            $hook = 'https://' . $domain . '/' . $webhookRestKey;
            Log::channel('telegram')->info('APRIL_ONLINE TEST', ['INSTALL' => ['hook' => $hook]]);
            $methodSmartInstall = '/crm.type.add.json';
            $url = $hook . $methodSmartInstall;

            // Проверка на массив
            if (is_array($googleData) && !empty($googleData['smarts'])) {
                $smarts = $googleData['smarts'];

                foreach ($smarts as $smart) {
                    $hookSmartInstallData = [
                        'fields' => [
                            'id' => $smart['entityTypeId'],
                            'title' => $smart['title'],
                            'entityTypeId' => $smart['entityTypeId'],
                            'code' => $smart['code'],
                            'isCategoriesEnabled' => 'Y',
                            'isStagesEnabled' => 'Y',
                            'isClientEnabled' => 'Y',
                            'isUseInUserfieldEnabled' => 'Y',
                            'isLinkWithProductsEnabled' => 'Y',
                            'isAutomationEnabled' => 'Y',
                            'isBizProcEnabled' => 'Y',
                            'availableEntityTypes' => ['COMPANY', 'DEAL', 'LEAD'],
                        ],
                    ];

                    // Используем post, чтобы отправить данные
                    $smartInstallResponse = Http::post($url, $hookSmartInstallData);
                    Log::channel('telegram')->info('APRIL_ONLINE TEST', ['INSTALL' => ['smartInstallResponse' => $smartInstallResponse]]);
                    $newSmart = BitrixController::getBitrixResponse($smartInstallResponse, 'productsSet');


                    $categories = InstallController::setCategories($hook, $smart['categories']);
                }
            } else {
                Log::channel('telegram')->error("Expected array from Google Sheets", ['googleData' => $googleData]);
            }
        } catch (\Exception $e) {
            Log::error('Error in installSmart', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return APIController::getError('An error occurred during installation', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }

        return APIController::getSuccess(['newSmart' => $newSmart, 'categories' => $categories]);
    }
    static function setFields(
        $parentType, //deal company lead smart list
        $type, //select, date, string,
        $title, //отображаемое имя
        $name, //имя в битрикс
        $bitrixId, //id в bitrix UF_CRM
        $bitrixCamelId, ////id в bitrix ufCrm
        $code, ////для доступа из app например comment или actions и будет list->field where code == actions
        $appOptions
    )

    //TODO fields
    // пербирает fields 
    // создает bitrixfield
    // 'title',
    // 'name',
    // 'code',
    // 'type',
    // 'bitrixId',
    // 'bitrixCamelId',
    // 'entity_id',
    // 'entity_type',
    // 'parent_type', //название типа филда в родительской модели напр list или для deal: offer | calling - к чему относится field
    // принадлежность к crm - создает связь bitrixfield

    //  если есть принадлежность к определенному app надо отобразить в parent_type
    // по entity_type bitrixfield будет связан с определенной моделью например BtxDeal BtxSmart
    // и в итоге будет доступен из Portal для Разных приложений
    // поскольку должна быть возможность  из Portal получить филды модели разной принадлежности
    // так например field НПА принадлежит к приложению конструктор
    // и должен иметь пометку например offer и будет доступен BtxDeal.offerFields()[0].action = crm.deal.add
    // в теории один field может принадлежать к разным app
    // например история работы - хотя он будет доступен только для приложения типа hook list

    //в приходящих перебираемых инициализационных филдах может содержаться информация для app
    // она должна приходить в специальном объекте app options
    // если один field принадлежит к разным app
    // у него могут быть опции для разных приложений
    //  [   option: {
    //         id:0
    //         name: isShowing
    //         app: offerApp
    //         type: boolean
    //         value:false

    //     }]
    // потом у field может быть много options которые будут доступны по типу приложения

    //основные поля BitrixField
    // 'title',
    // 'name',
    // 'code',
    // 'type',
    // 'bitrixId',
    // 'bitrixCamelId',
    // остальное должно лежать раскидано в в app options и содержать app type у каждой option

    // а также должно приходить тип сущности и 
    //как то надо определить id сущности
    // portal->deal->where('group', 'sales')->first()

    {
        // $domain = 'april-dev.bitrix24.ru';
        $domain = 'gsr.bitrix24.ru';
        $method = '/crm.deal.userfield.add';
        $hook = BitrixController::getHook($domain);
        $url = $hook . $method;
        // $fields = [ //string
        //     "FIELD_NAME" => "MY_STRING",
        //     "EDIT_FORM_LABEL" => "Моя строка",
        //     "LIST_COLUMN_LABEL" => "Моя строка",
        //     "USER_TYPE_ID" => "string",
        //     "XML_ID" => "MY_STRING",
        //     "SETTINGS" => ["DEFAULT_VALUE" => "Привет, мир!"]
        // ];
        $fields = [ //list
            "FIELD_NAME" => "TEST",
            "EDIT_FORM_LABEL" => "Тип Договора",
            "LIST_COLUMN_LABEL" => "Тип Договора",
            "USER_TYPE_ID" => "enumeration",
            "LIST" => [
                ["VALUE" => "Интернет"],
                ["VALUE" => "Проксима"],
                ["VALUE" => "Абонемент"],
                ["VALUE" => "Лицензия"],
                ["VALUE" => "Передача ключа"]

            ],
            "XML_ID" => "CONTRACT_TYPE",
            "SETTINGS" => ["LIST_HEIGHT" => 1],
            "ORDER" => 2
        ];

        $data = [
            'fields' => $fields
        ];
        $response = Http::post($url, $data);
        // $responseData = $response->json();
        $responseData = BitrixController::getBitrixResponse($response, 'BitrixDealDocumentService: getSmartItem');
        return APIController::getSuccess(['field' => $responseData]);

        //     "crm.deal.userfield.add",
        // {
        // 	fields:
        // 	{
        // 		"FIELD_NAME": "MY_STRING",
        // 		"EDIT_FORM_LABEL": "Моя строка",
        // 		"LIST_COLUMN_LABEL": "Моя строка",
        // 		"USER_TYPE_ID": "string",
        // 		"XML_ID": "MY_STRING",
        // 		"SETTINGS": { "DEFAULT_VALUE": "Привет, мир!" }
        // 	}


        // "FIELD_NAME": "MY_LIST",
        // 	"EDIT_FORM_LABEL": "Мой список",
        // 	"LIST_COLUMN_LABEL": "Мой список",
        // 	"USER_TYPE_ID": "enumeration",
        // 	"LIST": [ { "VALUE": "Элемент #1" },
        // 		{ "VALUE": "Элемент #2" },
        // 		{ "VALUE": "Элемент #3" },
        // 		{ "VALUE": "Элемент #4" },
        // 		{ "VALUE": "Элемент #5" } ],
        // 	"XML_ID": "MY_LIST",
        // 	"SETTINGS": { "LIST_HEIGHT": 3 }

        // },
        //     Набор полей  На данный момент:
        // ENTITY_ID
        // USER_TYPE_ID
        // FIELD_NAME
        // LIST_FILTER_LABEL
        // LIST_COLUMN_LABEL
        // EDIT_FORM_LABEL
        // ERROR_MESSAGE
        // HELP_MESSAGE
        // MULTIPLE
        // MANDATORY
        // SHOW_FILTER
        // SETTINGS
        // LIST - массив вида array("поле"=>"значение"[, ...]), содержащий описание пользовательского поля.
        // В том числе содержит поле LIST, которое содержит набор значений списка для пользовательских полей типа Список. Указывается при создании/обновлении поля. Каждое значение представляет собой массив с полями:

        // VALUE - значение элемента списка. Поле является обязательным в случае, когда создается новый элемент.
        // SORT - сортировка.
        // DEF - если равно Y, то элемент списка является значением по-умолчанию. Для множественного поля допустимо несколько DEF=Y. Для не множественного, дефолтным будет считаться первое.
        // XML_ID - внешний код значения. Параметр учитывается только при обновлении уже существующих значений элемента списка.
        // ID - идентификатор значения. Если он указан, то считается что это обновление существующего значения элемента списка, а не создание нового. Имеет смысл только при вызове методов *.userfield.update.
        // DEL - если равно Y, то существующий элемент списка будет удален. Применяется, если заполнен параметр ID.
    }

    static function setCategories(
        $hook,
        $categories
        // $type, //smart deal task lead
        // $group, //sales service  отдел
        // $name,
        // $title,
        // $bitrixId, //id в bitrix 23
        // $bitrixCamelId, //id в bitrix ufCrm
        // $code, //для доступа из app 
        // $isActive,
    ) {
        // crm.category.add({entityTypeId: number, fields: {}})
        // "id": 53,
        // "name": "Общее",
        // "sort": 500,
        // "entityTypeId": 170,
        // "isDefault": "N"



        // для связи с сущностью типа BtxDeal BtxList Smart
        // 'id' => 'sometimes|integer|exists:btx_categories,id',
        // 'type' => 'required|string', //smart deal task lead
        // 'group' => 'required|string',
        // 'name' => 'required|string',
        // 'title' => 'required|string',
        // 'entity_type' => 'required|string',
        // 'entity_id' => 'required|string',
        // 'parent_type' => 'required|string',
        // 'code' => 'required|string',
        // 'bitrixId' => 'required|string',
        // 'bitrixCamelId' => 'required|string',
        // 'isActive' => 'required|string',





        //         Сделка в направлении по умолчанию.
        // entityTypeId = \CCrmOwnerType::Deal = 2
        // entityTypeName = \CCrmOwnerType::DealName = 'DEAL'
        // entityTypeAbbr = \CCrmOwnerTypeAbbr::Deal = 'D'
        // userFieldEntityId = \CAllCrmDeal::USER_FIELD_ENTITY_ID = 'CRM_DEAL'
        // statusEntityId = 'DEAL_STAGE'
        // permissionEntity = 'DEAL'

        //         Сделка в направлении с идентификатором 3
        // entityTypeId = \CCrmOwnerType::Deal = 2
        // entityTypeName = \CCrmOwnerType::DealName = 'DEAL'
        // entityTypeAbbr = \CCrmOwnerTypeAbbr::Deal = 'D'
        // userFieldEntityId = \CAllCrmDeal::USER_FIELD_ENTITY_ID = 'CRM_DEAL'
        // statusEntityId = 'DEAL_STAGE_3'
        // permissionEntity = 'DEAL_C3'

        //         Компания
        // entityTypeId = \CCrmOwnerType::Company = 4
        // entityTypeName = \CCrmOwnerType::CompanyName = 'COMPANY'
        // entityTypeAbbr = \CCrmOwnerTypeAbbr::Company = 'CO'
        // userFieldEntityId = \CAllCrmCompany::USER_FIELD_ENTITY_ID = 'CRM_COMPANY'


        //         Смарт-процесс с идентификатором типа 128 и идентификатором 1 (колонка ID в b_crm_dynamic_type), направление по умолчанию которого имеет id = 20
        // entityTypeId = 128
        // entityTypeName = 'DYNAMIC_128'
        // entityTypeAbbr = 'T80'
        // userFieldEntityId = 'CRM_1'
        // statusEntityId = 'DYNAMIC_128_STAGE_20'
        // permissionEntity = 'DYNAMIC_128_C20'
        // suspendedEntityTypeId = 192
        // suspendedEntityTypeName = 'SUS_DYNAMIC_128
        // suspendedUserFieldEntityId = 'CRM_1_SPD'


        $methodCategoryInstall = '/crm.category.add.json';
        $url = $hook . $methodCategoryInstall;
        $results = [];
        // $hookCategoriesData1  =
        //     [
        //         "entityTypeId" => 134,

        //         'fields' => [
        //             'name' => 'Холодный обзвон',
        //             'title' => 'Холодный обзвон',
        //             "isDefault" => "N"
        //         ]
        //     ];
        // $hookCategoriesData2  =
        //     [
        //         "entityTypeId" => 134,

        //         'fields' => [
        //             'name' => 'Продажи',
        //             'title' => 'Продажи',
        //             "isDefault" => "Y"
        //         ]
        //     ];

        foreach ($categories as $index => $category) {
            if ($category['isNeedUpdate']) {
                $isDefault = $category['type'] === 'base';
                $hookCategoriesData  =  [
                    "entityTypeId" => $category['entityTypeId'],

                    'fields' => [
                        'name' => $category['name'],
                        'title' => $category['title'],
                        "isDefault" => $isDefault,
                        'sort' => $category['order'],
                        'id' => $category['bitrixId'],
                        'categoryId' => $category['bitrixId'],
                        'code' => $category['code'],
                    ]
                ];
                $smartCategoriesResponse = Http::get($url, $hookCategoriesData);
                // $bitrixResponseCategory = $smartCategoriesResponse->json();
                $bitrixResponseCategory = BitrixController::getBitrixResponse($smartCategoriesResponse, 'productsSet');
                Log::channel('telegram')->info('APRIL_ONLINE TEST', [
                    'INSTALL' => [
                        'bitrixResponseCategory' => $bitrixResponseCategory,


                    ]
                ]);
                array_push($results, $bitrixResponseCategory);
                $categoryId  = null;
                if (!empty($bitrixResponseCategory['category'])) {
                    if (!empty($bitrixResponseCategory['category']['id'])) {
                        $categoryId = $bitrixResponseCategory['category']['id'];

                        Log::channel('telegram')->info('APRIL_ONLINE TEST', [
                            'INSTALL' => [
                                'categoryId' => $categoryId,


                            ]
                        ]);
                        $stages = InstallController::setStages($hook, $category, $categoryId);

                        Log::channel('telegram')->info('APRIL_ONLINE TEST', [
                            'INSTALL' => [
                                'stages' => $stages,


                            ]
                        ]);
                        array_push($results, $stages);
                    }
                }
            }
        }
        return $results;
        // $smartCategoriesResponse1 = Http::get($url, $hookCategoriesData1);
        // $smartCategoriesResponse2 = Http::get($url, $hookCategoriesData2);



        // $bitrixResponseCategory1 = $smartCategoriesResponse1->json();
        // $bitrixResponseCategory2 = $smartCategoriesResponse2->json();
        // $category1Id = $bitrixResponseCategory1['result']['category']['id'];
        // $category2Id = $bitrixResponseCategory2['result']['category']['id'];
    }


    static function setStages(
        $hook,
        $category,
        $categoryId,
        // $entityTypeId, //id smart process или у deal - 2
        // $stages,
        // $category
        // $type, //smart deal task lead
        // $group, //sales service  отдел
        // $name,
        // $title,
        // $bitrixId, //id в bitrix 23
        // $color, //id в bitrix ufCrm
        // $code, //для доступа из app 
        // $isActive,
    ) {
        // crm.status.add({fields}
        //https://dev.1c-bitrix.ru/rest_help/crm/auxiliary/status/crm_status_add.php
        //  SMART FIELDS FOR STAGE CREATE
        // "fields": {
        //     "COLOR": "#1111AA",
        //     "NAME": "My new stage",
        //     "SORT": 250,
        //     "ENTITY_ID": "DYNAMIC_135_STAGE_20*", // *id категории хоть и написано STAGE 
        //     "STATUS_ID": "DT135_20:MY_STAGE_REST",  135 - id smart-процесса 20 - id категории  MY_STAGE_REST - id stage - онже bitrixId модели BtxStage
        // }

        //  DEAL FIELDS FOR STAGE CREATE
        // fields:
        // 	{ 
        // 		"ENTITY_ID": "DEAL_STAGE",		
        // 		"STATUS_ID": "DECISION",  // OFFER
        // 		"NAME": "Принятие решения",
        // 		"SORT": 70
        // 	}


        //  DEAL FIELDS FOR STAGE CREATE not default = with category
        // fields:
        // { 
        //     "ENTITY_ID": "DEAL_STAGE_1",        
        //     "STATUS_ID": "DECISION",
        //     "NAME": "Принятие решения",
        //     "SORT": 70
        // }



        //         Работа со стадиями смарт-процессов осуществляется через общий набор методов crm.status.*


        // ENTITY_ID
        // Поле ENTITY_ID для стадий смарт-процессов имеет следующий вид: DYNAMIC_{entityTypeId}_STAGE_{categoryId},

        // где:


        // {entityTypeId} - идентификатор типа CRM смарт-процесса;
        // {categoryId} - идентификатор направления, к которому относится стадия.

        // STATUS_ID
        // Поле STATUS_ID для стадий смарт-процессов должно имет префикс DT{entityTypeId}_{categoryId},

        // где


        // {entityTypeId} - идентификатор типа CRM смарт-процесса
        // {categoryId} - идентификатор направления, к которому относится стадия


        // ..............................................................entityTypeId
        // https://dev.1c-bitrix.ru/api_d7/bitrix/crm/dynamic/index.php
        //         Лид
        // entityTypeId = \CCrmOwnerType::Lead = 1
        // entityTypeName = \CCrmOwnerType::LeadName = 'LEAD'
        // entityTypeAbbr = \CCrmOwnerTypeAbbr::Lead = 'L'
        // userFieldEntityId = \CAllCrmLead::USER_FIELD_ENTITY_ID = 'CRM_LEAD'


        $currentstagesMethod = '/crm.status.list.json';
        $url = $hook . $currentstagesMethod;
        $hookCurrentStagesData = [
            'entityTypeId' => $category['entityTypeId'],
            'entityId' => 'STATUS',
            'categoryId' => $categoryId,
            'filter' => ['ENTITY_ID' => 'DYNAMIC_' . $category['entityTypeId'] . '_STAGE_' . $categoryId]

        ];


        // // Log::info('CURRENT STAGES GET 134', ['currentStagesResponse' => $hookCurrentStagesData]);
        $currentStagesResponse = Http::post($url, $hookCurrentStagesData);
        $currentStages = $currentStagesResponse['result'];
        // Log::info('CURRENT STAGES GET 134', ['currentStages' => $currentStages]);



        $callStages = [
            [
                'title' => 'Создан',
                'name' => 'NEW',
                'color' => '#832EF9',
                'sort' => 10,
            ],
            [
                'title' => 'Запланирован',
                'name' => 'PLAN',
                'color' => '#BA8BFC',
                'sort' => 20,
            ],
            [
                'title' => 'Просрочен',
                'name' => 'PREPARATION',
                'color' => '#A262FC',
                'sort' => 30,
            ],
            [
                'title' => 'Завершен без результата',
                'name' => 'CLIENT',
                'color' => '#7849BB',
                'sort' => 40,
            ],

        ];
        $resultStages = [];
        if (!empty($category['stages'])) {
            $stages = $category['stages'];
            foreach ($stages as $stage) {

                //TODO: try get stage if true -> update stage else -> create
                $statusId = 'DT' . $stage['entityTypeId'] . '_' . $categoryId;
                $NEW_STAGE_STATUS_ID = $statusId . ':' . $stage['bitrixId'];
                $dynamicId = 'DYNAMIC_' . $stage['entityTypeId'] . '_STAGE' . $categoryId;

                $isExist = false;
                foreach ($currentStages as $index => $currentStage) {
                    // Log::info('currentStage ITERABLE', ['STAGE STATUS ID' => $currentStage['STATUS_ID']]);
                    if ($currentStage['STATUS_ID'] === $NEW_STAGE_STATUS_ID) {
                        // Log::info('EQUAL STAGE', ['EQUAL STAGE' => $currentStage['STATUS_ID']]);
                        $isExist = $currentStage['ID'];
                    }
                }

                if ($isExist) { //если стадия с таким STATUS_ID существует - надо сделать update
                    $methodStageInstall = '/crm.status.update.json';
                    $url = $hook . $methodStageInstall;
                    $hookStagesDataCalls  =
                        [

                            'ID' => $isExist,
                            'fields' => [
                                // 'STATUS_ID' => 'DT134_' . $category1Id . ':' . $callStage['name'],
                                // "ENTITY_ID" => 'DYNAMIC_134_STAGE_' . $category1Id,
                                'NAME' => $stage['title'],
                                'TITLE' => $stage['title'],
                                'SORT' => $stage['order'],
                                'COLOR' => $stage['color']
                                // "isDefault" => $callStage['title'] === 'Создан' ? "Y" : "N"
                            ]
                        ];
                } else {
                    $methodStageInstall = '/crm.status.add.json';
                    $url = $hook . $methodStageInstall;
                    $hookStagesDataCalls  =
                        [

                            'statusId' =>  $statusId, //'DT134_' . $categoryId,
                            'fields' => [
                                'STATUS_ID' => $NEW_STAGE_STATUS_ID, //'DT134_' . $categoryId . ':' . $callStage['name'],
                                "ENTITY_ID" => $dynamicId, //'DYNAMIC_134_STAGE_' . $categoryId,
                                'NAME' => $stage['title'],
                                'TITLE' => $stage['title'],
                                'SORT' => $stage['order'],
                                'COLOR' => $stage['color']
                                // "isDefault" => $callStage['title'] === 'Создан' ? "Y" : "N"
                            ]
                        ];
                }
                $smartStageResponse = Http::post($url, $hookStagesDataCalls);
                $stageResultResponse = BitrixController::getBitrixResponse($smartStageResponse, 'productsSet');
                array_push($resultStages, $stageResultResponse);
                // $bitrixResponseStage = $smartStageResponse->json();
                // Log::info('SUCCESS SMART INSTALL', ['stage_response' => $bitrixResponseStage]);
            }

            return $resultStages;
        }
    }
}
