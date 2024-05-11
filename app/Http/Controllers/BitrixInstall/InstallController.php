<?php

namespace App\Http\Controllers\BitrixInstall;

use App\Http\Controllers\APIController;
use App\Http\Controllers\BitrixController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\PortalController;
use App\Models\Portal;
use App\Models\Smart;
use FontLib\Table\Type\name;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InstallController extends Controller
{
    public static function installSmart(
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
            $portal = PortalController::innerGetPortal($domain);
            $newSmart = null;
            $categories = null;
            $url = 'https://script.google.com/macros/s/' . $token . '/exec';
            $response = Http::get($url);

            $resultSmarts = [];

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
            $portalId = $portal['portal']['id'];
            $hook = 'https://' . $domain . '/' . $webhookRestKey;


            // Проверка на массив
            if (is_array($googleData) && !empty($googleData['smarts'])) {
                $smarts = $googleData['smarts'];

                foreach ($smarts as $smart) {
                    $currentPortalSmart = null;
                    $currentBtxSmart = null;
                    $currentBtxSmartId = null;

                    $methodSmartInstall = '/crm.type.list.json';
                    $url = $hook . $methodSmartInstall;
                    $typeGetData = [
                        'filter' => [
                            'entityTypeId' => $smart['entityTypeId'],
                        ]
                    ];
                    $getsmartResponse = Http::post($url, $typeGetData);

                    $getSmarts = BitrixController::getBitrixResponse($getsmartResponse, 'get Smart');

                    if (!empty($getSmarts)) {
                        if (!empty($getSmarts['types'])) {
                            $currentBtxSmart = $getSmarts['types'][0];
                            $currentBtxSmartId = $currentBtxSmart['entityTypeId'];
                        }
                    }


                    if (!$currentBtxSmart) {
                        $methodSmartInstall = '/crm.type.add.json';
                    } else {
                        $methodSmartInstall = '/crm.type.update.json';
                        $hookSmartInstallData = [
                            'id' => $currentBtxSmart['id'],
                            'fields' => []
                        ];
                    }

                    $url = $hook . $methodSmartInstall;
                    $hookSmartInstallData['fields'] = [
                        // 'id' => $smart['entityTypeId'],
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

                        "isCrmTrackingEnabled" => "Y",
                        "isMycompanyEnabled" => "Y",
                        "isDocumentsEnabled" => "Y",
                        "isSourceEnabled" => "Y",
                        "isObserversEnabled" => "Y",
                        "isRecyclebinEnabled" => "Y",

                        "isChildrenListEnabled" => "Y",
                        "isSetOpenPermissions" => "Y",
                        "linkedUserField" =>  [
                            'CALENDAR_EVENT|UF_CRM_CAL_EVENT',
                            'TASKS_TASK|UF_CRM_TASK',
                            'TASKS_TASK_TEMPLATE|UF_CRM_TASK',

                        ]
                    ];


                    // Используем post, чтобы отправить данные
                    $smartInstallResponse = Http::post($url, $hookSmartInstallData);

                    $newSmart = BitrixController::getBitrixResponse($smartInstallResponse, 'newSmart');
                    if (isset($newSmart['type'])) {
                        $currentBtxSmart = $newSmart['type'];
                    }
                    $currentBtxSmartId = $currentBtxSmart['entityTypeId'];







                    if (!empty($currentBtxSmart)) {
                        if (!empty($currentBtxSmart['entityTypeId'] && !empty($currentBtxSmart['code']))) {
                            $currentPortalSmart = Portal::where('domain', $domain)->first()->smarts()->where('type', $currentBtxSmart['code'])->first();
                            $smartEntityTypeId = $smart['entityTypeId'];
                            if (!$currentPortalSmart) {

                                $currentPortalSmart = new Smart();
                                $currentPortalSmart->portal_id = $portalId;
                            }
                            $currentPortalSmart->type = $smart['code'];
                            $currentPortalSmart->group = $smart['code'];
                            $currentPortalSmart->name = $smart['code'];
                            $currentPortalSmart->title = $smart['title'];
                            $currentPortalSmart->bitrixId = $smartEntityTypeId;
                            $currentPortalSmart->entityTypeId = $smartEntityTypeId;
                            $currentPortalSmart->forStageId = $smartEntityTypeId;
                            $currentPortalSmart->forStage = 'DT' . $smartEntityTypeId . '_';
                            $currentPortalSmart->forFilterId = $smartEntityTypeId;
                            $currentPortalSmart->forFilter = 'DYNAMIC_' . $smartEntityTypeId . '_';
                            $currentPortalSmart->crmId = $currentBtxSmart['id'];
                            $currentPortalSmart->crm = 'WARNING' . $smartEntityTypeId;
                            $currentPortalSmart->save();
                        }
                    }




                    // $categories = InstallController::setCategories($hook, $smart['categories']);
                    array_push($resultSmarts, $currentBtxSmart);
                    
                    InstallFieldsController::setFields($token, 'smart', $currentBtxSmart, $currentPortalSmart);
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

        return APIController::getSuccess(['resultSmarts' => $resultSmarts, 'categories' => $categories]);
    }


    //     Настройки связей
    // Настройки связей отдаются по ключу relations в следующем виде:

    // {
    // 	"parent": [],
    // 	"child": []
    // }
    // parent - настройки привязок к этому смарт-процессу;
    // child - настройки привязок этого смарт-процесса к другим разделам.
    // где каждый элемент массива имеет следующую структуру с описанием связи:
    // {
    //     "entityTypeId": number,
    //     "isChildrenListEnabled": boolean,
    //     "isPredefined": boolean
    // }


    //     Если поле isUseInUserfieldEnabled установлено в true, 
    // то можно передать по ключу linkedUserFields набор полей, 
    // в которых должен отображаться этот смарт-процесс.

    // 'CALENDAR_EVENT|UF_CRM_CAL_EVENT' - событие в календаре.
    // 'TASKS_TASK|UF_CRM_TASK' - задачи.
    // 'TASKS_TASK_TEMPLATE|UF_CRM_TASK' - шаблон задачи.
    // Если в поле isUseInUserfieldEnabled передать false, то все настроенные привязки будут отключены.






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




        // Смарт-процесс с идентификатором типа 128 и идентификатором 1 (колонка ID в b_crm_dynamic_type), направление по умолчанию которого имеет id = 20
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


        // }
        $defaultCategoryId = null;
        $results = [];

        foreach ($categories as $category) {
            $categoryName = $category['name'];
            // $isDefault = $category['type'] === 'base' ? 'Y' : 'N';

            // Ищем, есть ли уже категория по умолчанию

            $methodCategoryInstall = '/crm.category.add.json';
            $urlInstall = $hook . $methodCategoryInstall;
            $categoryId = null;


            $hookCategoriesData = [
                'entityTypeId' => $category['entityTypeId'],
                // 'statusEntityId' => 'DEAL_STAGE_3',
                'fields' => [
                    'name' => $categoryName,
                    'title' => $category['title'],
                    // 'isDefault' => $isDefault,
                    'sort' => $category['order'],
                    'code' => $category['code']
                ]
            ];



            $smartCategoriesResponse = Http::post($urlInstall, $hookCategoriesData);
            $bitrixResponseCategory = BitrixController::getBitrixResponse($smartCategoriesResponse, 'category');

            // if (isset($bitrixResponseCategory['id'])) {
            //     $categoryId = $bitrixResponseCategory['id'];
            // }
            if (!empty($bitrixResponseCategory['category'])) {
                if (isset($bitrixResponseCategory['category']['id'])) {
                    $categoryId = $bitrixResponseCategory['category']['id'];
                }
            }





            // Создаем или обновляем стадии
            $stages = InstallController::setStages($hook, $category, $categoryId);
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



        $currentstagesMethod = '/crm.status.list.json';
        $url = $hook . $currentstagesMethod;
        $hookCurrentStagesData = [
            'filter' => ['ENTITY_ID' => 'DYNAMIC_' . $category['entityTypeId'] . '_STAGE_' . $categoryId]
        ];

        $currentStagesResponse = Http::post($url, $hookCurrentStagesData);
        $currentStages = $currentStagesResponse->json()['result'];

        $resultStages = [];
        if (!empty($category['stages'])) {
            $stages = $category['stages'];
            foreach ($stages as $stage) {

                $statusId = 'DT' . $stage['entityTypeId'] . '_' . $categoryId . ':' . $stage['bitrixId'];
                $dynamicId = 'DYNAMIC_' . $stage['entityTypeId'] . '_STAGE_' . $categoryId;

                $isExist = false;
                foreach ($currentStages as $currentStage) {
                    if ($currentStage['STATUS_ID'] === $statusId) {
                        $isExist = $currentStage['ID'];
                    }
                }

                if ($isExist) {
                    // Update stage
                    $methodStageInstall = '/crm.status.update.json';
                    $url = $hook . $methodStageInstall;
                    $hookStagesDataCalls = [
                        'ID' => $isExist,
                        'fields' => [
                            'NAME' => $stage['title'],
                            'TITLE' => $stage['title'],
                            'SORT' => $stage['order'],
                            'COLOR' => $stage['color']
                        ]
                    ];
                } else {
                    // Create stage
                    $methodStageInstall = '/crm.status.add.json';
                    $url = $hook . $methodStageInstall;
                    $hookStagesDataCalls = [
                        'statusId' => $statusId,
                        'fields' => [
                            'STATUS_ID' => $statusId,
                            'ENTITY_ID' => $dynamicId,
                            'NAME' => $stage['title'],
                            'TITLE' => $stage['title'],
                            'SORT' => $stage['order'],
                            'COLOR' => $stage['color']
                        ]
                    ];
                }

                Log::channel('telegram')->info("categoryId", [
                    'Stages Data' => $hookStagesDataCalls,
                ]);

                $smartStageResponse = Http::post($url, $hookStagesDataCalls);
                $stageResultResponse = BitrixController::getBitrixResponse($smartStageResponse, 'stages install');
                array_push($resultStages, $stageResultResponse);
            }
        }

        return $resultStages;
    }
}
