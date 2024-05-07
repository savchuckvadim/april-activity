<?php

namespace App\Http\Controllers\BitrixInstall;

use App\Http\Controllers\APIController;
use App\Http\Controllers\BitrixController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\PortalController;
use App\Http\Resources\PortalResource;
use App\Models\Portal;
use FontLib\Table\Type\name;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InstallDealController extends Controller
{
    public static function installDealCtaegories(
        // $domain,
        // $smarts
        $token
    ) {
        // $domain = 'gsr.bitrix24.ru';
        $domain = 'april-dev.bitrix24.ru';

        // $method = '/crm.deal.userfield.add';
        $hook = BitrixController::getHook($domain);
        // Log::channel('telegram')->info('APRIL_ONLINE TEST', ['hook' => ['hook' => $hook]]);

        // $url = $hook . $method;
        //1) создает смарт процесс и сам задает  "entityTypeId" => 134,

        //3) записывает стадии и направления ввиде одного объекта json связь portal-smart

        // $initialData = Http::get(
        //     ''
        // );


        try {
            $portal = Portal::where('domain', $domain)->first();

            // $portal = ;
            // $portal = PortalController::innerGetPortal($domain);
            $portalDeal = null;

            $portalDealCategories = null;
            $portalStages = [];
            if (isset($portal->deals)) {
                $portalDeals = $portal->deals;
                $portalDeal = $portalDeals[0];
                $portalDealCategories = $portalDeal->categories;

                foreach ($portalDealCategories as $portDealCategory) {
                    foreach ($portDealCategory->stages as $portDealStage) {
                        array_push($portalStages, $portDealStage);
                    }
                }
            }
            Log::channel('telegram')->info('APRIL_ONLINE TEST', [
                'INSTALL' => [
                    // 'portal' => $portal,
                    // 'portalDeal' => $portalDeal,
                    'portalDealCategories' => $portalDealCategories,
                    'portalStages' => $portalStages,

                ]
            ]);





            $categories = null;
            $url = 'https://script.google.com/macros/s/' . $token . '/exec';
            $response = Http::get($url);
            $googleData = 'googleData';
            if ($response->successful()) {
                $googleData = $response->json();
                Log::channel('telegram')->info("googleData", [
                    'googleData' => $googleData,

                ]);
            } else {
                Log::channel('telegram')->info("Failed to retrieve data from Google Sheets", [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return response(['resultCode' => 1, 'message' => 'Error retrieving data'], 500);
            }




            $webhookRestKey = $portal->C_REST_WEB_HOOK_URL;
            $hook = 'https://' . $domain . '/' . $webhookRestKey;
            // Log::channel('telegram')->info('APRIL_ONLINE TEST', ['INSTALL' => ['hook' => $hook]]);
            // $methodSmartInstall = '/crm.type.add.json';
            // $url = $hook . $methodSmartInstall;
            Log::channel('telegram')->info('APRIL_ONLINE TEST', ['INSTALL' => [
                'googleData' => $googleData,

            ]]);
            // Проверка на массив
            if (!empty($googleData['deals']) && !empty($googleData['fields'])) {
                $deals = $googleData['deals'];
                $fields = $googleData['fields'];
                Log::channel('telegram')->info('APRIL_ONLINE TEST', ['INSTALL' => [
                    'fields' => $fields,
                    'deals' => $deals
                ]]);

                foreach ($deals as $deal) {
                    // $hookSmartInstallData = [
                    //     'fields' => [
                    //         'id' => $smart['entityTypeId'],
                    //         'title' => $smart['title'],
                    //         'entityTypeId' => $smart['entityTypeId'],
                    //         'code' => $smart['code'],
                    //         'isCategoriesEnabled' => 'Y',
                    //         'isStagesEnabled' => 'Y',
                    //         'isClientEnabled' => 'Y',
                    //         'isUseInUserfieldEnabled' => 'Y',
                    //         'isLinkWithProductsEnabled' => 'Y',
                    //         'isAutomationEnabled' => 'Y',
                    //         'isBizProcEnabled' => 'Y',
                    //         'availableEntityTypes' => ['COMPANY', 'DEAL', 'LEAD'],
                    //     ],
                    // ];

                    // Используем post, чтобы отправить данные
                    // $smartInstallResponse = Http::post($url, $hookSmartInstallData);

                    // $newSmart = BitrixController::getBitrixResponse($smartInstallResponse, 'productsSet');


                    // $categories = InstallDealController::setCategories($hook, $deal['categories']);
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

        return APIController::getSuccess(['categories' => $categories]);
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


        $methodCategoryList = '/crm.category.list.json';
        $url = $hook . $methodCategoryList;

        // Получаем список существующих категорий
        // $currentCategoriesResponse = Http::post($url, [
        //     'entityTypeId' => $categories[0]['entityTypeId'],
        //     'filter' => [
        //         'entityTypeId' => $categories[0]['entityTypeId']
        //     ]
        // ]);

        // $currentCategories = BitrixController::getBitrixResponse($currentCategoriesResponse, 'crm.category.list');
        // if(!empty($currentCategories['items'])){
        //     $currentCategories = $currentCategories['items'];
        // }
        // if(!empty($currentCategories['categories'])){
        //     $currentCategories = $currentCategories['categories'];
        // }
        $defaultCategoryId = null;
        $results = [];

        foreach ($categories as $category) {
            $categoryName = $category['name'];
            $isDefault = $category['type'] === 'base' ? 'Y' : 'N';

            // Ищем, есть ли уже категория по умолчанию
            $existingDefaultCategory = null;
            // if (!empty($currentCategories)) {
            //     foreach ($currentCategories as $currentCategory) {
            //         if(isset($currentCategory['isDefault'])){
            //             if ($currentCategory['isDefault'] === 'Y') {
            //                 $existingDefaultCategory = $currentCategory;
            //                 break;
            //             }
            //         }

            //     }
            // }


            // Если текущая категория по умолчанию не совпадает с требуемой
            // if ($existingDefaultCategory && $existingDefaultCategory['name'] !== $categoryName) {
            //     // Обновляем существующую категорию, делая ее не по умолчанию
            //     $methodCategoryUpdate = '/crm.category.update.json';
            //     $urlUpdate = $hook . $methodCategoryUpdate;
            //     Http::post($urlUpdate, [
            //         'id' => $existingDefaultCategory['id'],
            //         'entityTypeId' => $category['entityTypeId'],
            //         'fields' => [

            //             'isDefault' => 'N'
            //         ]
            //     ]);
            // }

            // // Добавляем или обновляем категорию
            // if ($existingDefaultCategory && $existingDefaultCategory['name'] === $categoryName) {
            //     // Обновляем существующую категорию
            //     $methodCategoryInstall = '/crm.category.update.json';
            //     $urlInstall = $hook . $methodCategoryInstall;
            //     $categoryId = $existingDefaultCategory['id'];
            // } else {
            // Добавляем новую категорию
            $methodCategoryInstall = '/crm.category.add.json';
            $urlInstall = $hook . $methodCategoryInstall;
            $categoryId = null;
            // }

            $hookCategoriesData = [
                'entityTypeId' => $category['entityTypeId'],
                'fields' => [
                    'name' => $categoryName,
                    'title' => $category['title'],
                    'isDefault' => $isDefault,
                    'sort' => $category['order'],
                    'code' => $category['code']
                ]
            ];

            // if ($categoryId !== null) {
            //     $hookCategoriesData['id'] = $categoryId;
            // }

            $smartCategoriesResponse = Http::post($urlInstall, $hookCategoriesData);
            $bitrixResponseCategory = BitrixController::getBitrixResponse($smartCategoriesResponse, 'category');

            Log::channel('telegram')->info('APRIL_ONLINE TEST', ['INSTALL' => ['bitrixResponseCategory' => $bitrixResponseCategory]]);
            // if (isset($bitrixResponseCategory['id'])) {
            //     $categoryId = $bitrixResponseCategory['id'];
            // }
            if (!empty($bitrixResponseCategory['category'])) {
                if (isset($bitrixResponseCategory['category']['id'])) {
                    $categoryId = $bitrixResponseCategory['category']['id'];
                }
            }
            // $categoryId = $bitrixResponseCategory['result'];

            // if ($isDefault === 'Y') {
            //     $defaultCategoryId = $categoryId;
            // }

            // Log::channel('telegram')->info('APRIL_ONLINE TEST', [
            //     'INSTALL' => [
            //         'bitrixResponseCategory' => $bitrixResponseCategory
            //     ]
            // ]);

            // Удаляем ненужные стадии
            // $currentStagesResponse = Http::post($url, [
            //     'entityTypeId' => $category['entityTypeId'],
            //     'filter' => [
            //         'entityTypeId' => $category['entityTypeId'],
            //         'categoryId' => $categoryId
            //     ]
            // ]);
            // $bitrixResponseCategory = BitrixController::getBitrixResponse($currentStagesResponse, 'category currentStagesResponse');
            // $currentStages = $bitrixResponseCategory;
            // if (isset($currentStages['items'])) {
            //     $currentStages = $currentStages['items'];
            // }
            // if (!empty($currentStages)) {
            //     foreach ($currentStages as $currentStage) {
            //         if (!in_array($currentStage['STATUS_ID'], array_column($category['stages'], 'bitrixId'))) {
            //             $methodStageDelete = '/crm.status.delete.json';
            //             $urlDeleteStage = $hook . $methodStageDelete;
            //             Http::post($urlDeleteStage, ['ID' => $currentStage['ID']]);
            //         }
            //     }
            // }

            Log::channel('telegram')->info("categoryId", [
                'categoryId' => $categoryId,

            ]);
            // Создаем или обновляем стадии
            $stages = InstallDealController::setStages($hook, $category, $categoryId);
            array_push($results, $stages);
        }

        return $results;
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


        // $currentstagesMethod = '/crm.status.list.json';
        // $url = $hook . $currentstagesMethod;
        // $hookCurrentStagesData = [
        //     'entityTypeId' => $category['entityTypeId'],
        //     'entityId' => 'STATUS',
        //     'categoryId' => $categoryId,
        //     'filter' => ['ENTITY_ID' => 'DYNAMIC_' . $category['entityTypeId'] . '_STAGE_' . $categoryId]

        // ];


        // // // Log::info('CURRENT STAGES GET 134', ['currentStagesResponse' => $hookCurrentStagesData]);
        // $currentStagesResponse = Http::post($url, $hookCurrentStagesData);
        // $currentStages = $currentStagesResponse['result'];
        // Log::info('CURRENT STAGES GET 134', ['currentStages' => $currentStages]);



        // $callStages = [
        //     [
        //         'title' => 'Создан',
        //         'name' => 'NEW',
        //         'color' => '#832EF9',
        //         'sort' => 10,
        //     ],
        //     [
        //         'title' => 'Запланирован',
        //         'name' => 'PLAN',
        //         'color' => '#BA8BFC',
        //         'sort' => 20,
        //     ],
        //     [
        //         'title' => 'Просрочен',
        //         'name' => 'PREPARATION',
        //         'color' => '#A262FC',
        //         'sort' => 30,
        //     ],
        //     [
        //         'title' => 'Завершен без результата',
        //         'name' => 'CLIENT',
        //         'color' => '#7849BB',
        //         'sort' => 40,
        //     ],

        // ];
        $resultStages = [];
        if (!empty($category['stages'])) {
            $stages = $category['stages'];
            foreach ($stages as $stage) {

                //TODO: try get stage if true -> update stage else -> create
                $entityId = 'DEAL_STAGE_' . $categoryId;
                // $NEW_STAGE_STATUS_ID = $entityId . ':' . $stage['bitrixId'];
                // $dynamicId = 'DYNAMIC_' . $stage['entityTypeId'] . '_STAGE_' . $categoryId;

                // $isExist = false;
                // foreach ($currentStages as $index => $currentStage) {
                //     // Log::info('currentStage ITERABLE', ['STAGE STATUS ID' => $currentStage['STATUS_ID']]);
                //     if ($currentStage['STATUS_ID'] === $stage['bitrixId']) {
                //         // Log::info('EQUAL STAGE', ['EQUAL STAGE' => $currentStage['STATUS_ID']]);
                //         $isExist = $currentStage['ID'];
                //     }
                // }

                // if ($isExist) { //если стадия с таким STATUS_ID существует - надо сделать update
                //     $methodStageInstall = '/crm.status.update.json';
                //     $url = $hook . $methodStageInstall;
                //     $hookStagesDataCalls  =
                //         [

                //             'ID' => $isExist,
                //             'fields' => [

                //                 'NAME' => $stage['title'],
                //                 'TITLE' => $stage['title'],
                //                 'SORT' => $stage['order'],
                //                 'COLOR' => $stage['color']
                //                 // "isDefault" => $callStage['title'] === 'Создан' ? "Y" : "N"
                //             ]
                //         ];
                // } else {
                $methodStageInstall = '/crm.status.add.json';
                $url = $hook . $methodStageInstall;
                $hookStagesDataCalls  =
                    [

                        // 'statusId' =>  $statusId, //'DT134_' . $categoryId,
                        'fields' => [
                            'STATUS_ID' => $stage['bitrixId'], //"DECISION",  // OFFER
                            "ENTITY_ID" => $entityId,   // "DEAL_STAGE_1",
                            'NAME' => $stage['title'],
                            'TITLE' => $stage['title'],
                            'SORT' => $stage['order'],
                            'COLOR' => $stage['color']
                            // "isDefault" => $callStage['title'] === 'Создан' ? "Y" : "N"
                        ]
                    ];
                // }
                Log::channel('telegram')->info("categoryId", [
                    'hookStagesDataCalls' => $hookStagesDataCalls,

                ]);
                $smartStageResponse = Http::post($url, $hookStagesDataCalls);
                $stageResultResponse = BitrixController::getBitrixResponse($smartStageResponse, 'stages install');

                Log::channel('telegram')->info("categoryId", [
                    'stageResultResponse' => $stageResultResponse,

                ]);
                array_push($resultStages, $stageResultResponse);
                // $bitrixResponseStage = $smartStageResponse->json();
                // Log::info('SUCCESS SMART INSTALL', ['stage_response' => $bitrixResponseStage]);
            }

            return $resultStages;
        }
    }
}
