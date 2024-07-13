<?php

namespace App\Http\Controllers\BitrixInstall\RPA;

use App\Http\Controllers\APIController;
use App\Http\Controllers\BitrixController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\PortalController;
use App\Models\BtxCategory;
use App\Models\BtxRpa;
use App\Models\BtxStage;
use App\Models\Portal;
use App\Models\Smart;
use FontLib\Table\Type\name;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InstallRPAController extends Controller
{
    public static function installRPA(
        $domain,
        // $smarts
        $token
    ) {

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
            $portalId = null;
            $portalDealId = null;
            $portalDeal = null;
            if ($portal && isset($portal->id)) {
                $portalId = $portal->id;
            }
            $webhookRestKey = $portal->getHook();
            $hook = 'https://' . $domain . '/' . $webhookRestKey;
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






            // Проверка на массив
            if (is_array($googleData) && !empty($googleData['rpa'])) {
                $rpas = $googleData['rpa'];

                foreach ($rpas as $rpa) {

                    $currentPortalRPA = null;
                    $currentBtxRPA = null;
                    $currentBtxRPAId = null;

                    $methodRPAInstall = '/rpa.type.list.json';
                    $url = $hook . $methodRPAInstall;
                    $typeGetData = [
                        'filter' => [
                            'title' => $rpa['title'],
                        ]
                    ];
                    $getrpasResponse = Http::post($url, $typeGetData);

                    Log::channel('telegram')->info('RPA ONLINE ADMIN', [
                        'getrpasResponse' => $getrpasResponse
                    ]);

                    Log::info('RPA ONLINE ADMIN', [
                        'getrpasResponse' => $getrpasResponse
                    ]);
                    $getrpas = BitrixController::getBitrixResponse($getrpasResponse, 'get RPA');

                    if (!empty($getrpas)) {
                        if (!empty($getrpas['types'])) {
                            $currentBtxRPA = $getrpas['types'][0];
                        }
                    }
                    $hookRPAInstallData = [
                        'fields' => []
                    ];

                    if (!$currentBtxRPA) {
                        $methodRPAInstall = '/rpa.type.add.json';
                    } else {
                        $methodRPAInstall = '/rpa.type.update.json';
                        $hookRPAInstallData = [
                            'id' => $currentBtxRPA['id'],
                            'fields' => []
                        ];
                    }

                    $url = $hook . $methodRPAInstall;
                    $hookRPAInstallData['fields'] = [
                        // 'id' => $smart['entityTypeId'],
                        'title' => $rpa['title'],
                        // 'entityTypeId' => $rpa['entityTypeId'],
                        // 'code' => $rpa['code'],
                        'image' => $rpa['image'],




                    ];


                    // Используем post, чтобы отправить данные
                    $rpaInstallResponse = Http::post($url, $hookRPAInstallData);

                    $newRPA = BitrixController::getBitrixResponse($rpaInstallResponse, 'newSmart');
                    if (isset($newRPA['type'])) {
                        $currentBtxRPA = $newRPA['type'];
                    }
                    // $currentBtxRPAId = $currentBtxRPA['entityTypeId'];


                    Log::channel('telegram')->info('RPA ONLINE ADMIN', [
                        'currentBtxRPA' => $currentBtxRPA
                    ]);

                    Log::info('RPA ONLINE ADMIN', [
                        'currentBtxRPA' => $currentBtxRPA
                    ]);




                    if (!empty($currentBtxRPA)) {
                        if (!empty($currentBtxRPA['title'])) {
                            $currentPortalRPA = $portal->rpas()->where('title', $currentBtxRPA['title'])->first();
                            // $smartEntityTypeId = $rpa['entityTypeId'];
                            if (!$currentPortalRPA) {

                                $currentPortalRPA = new BtxRpa();
                                $currentPortalRPA->portal_id = $portalId;
                            }
                            $currentPortalRPA->type = $rpa['type'];
                            $currentPortalRPA->code = $rpa['code'];
                            $currentPortalRPA->name = $rpa['name'];
                            $currentPortalRPA->title = $rpa['title'];
                            $currentPortalRPA->description = $currentBtxRPA['id'];
                            $currentPortalRPA->typeId = $currentBtxRPA['id'];

                            $currentPortalRPA->bitrixId = $currentBtxRPA['id'];
                            $currentPortalRPA->entityTypeId =  $currentBtxRPA['id'];
                            $currentPortalRPA->forStageId = $currentBtxRPA['id'];

                            $currentPortalRPA->forFilterId =  $currentBtxRPA['id'];
                            $currentPortalRPA->crmId = $currentBtxRPA['id'];

                            $currentPortalRPA->save();



                            Log::channel('telegram')->info('RPA ONLINE ADMIN', [
                                'currentPortalRPA' => $currentPortalRPA
                            ]);

                            Log::info('RPA ONLINE ADMIN', [
                                'currentPortalRPA' => $currentPortalRPA
                            ]);
                        }
                    }




                    // $categories = InstallController::setCategories($hook, $smart['categories'], $currentBtxSmart, $currentPortalSmart);
                    // array_push($resultSmarts, $currentBtxSmart);

                    // InstallRPAFieldsController::setFields($token, 'smart', $currentBtxRPA, $currentPortalSmart);
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
        $categories,
        $currentBtxSmart,
        $currentPortalSmart
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



        $currentPortalCategories = $currentPortalSmart->categories;


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
            $btxCategoryId = null;


            $hookCategoriesData = [
                'entityTypeId' => $category['entityTypeId'],
                // 'statusEntityId' => 'DEAL_STAGE_3',
                'fields' => [
                    'name' => $categoryName,
                    'title' => $category['title'],
                    'isDefault' => $category['isDefault'],

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
                $bitrixResponseCategory = $bitrixResponseCategory['category'];
                if (isset($bitrixResponseCategory['id'])) {
                    $btxCategoryId = $bitrixResponseCategory['id'];
                }
            }

            if ($btxCategoryId) {

                //обновляем категорию в БД
                $portalCategory = null;
                foreach ($currentPortalCategories as $currentPortalCategory) { //перебираем категории сделки привязанной к порталу db
                    if (!empty($currentPortalCategory) && isset($currentPortalCategory['code'])) {
                        if ($currentPortalCategory['code'] == $category['code']) {

                            $portalCategory = BtxCategory::find($currentPortalCategory['id']);
                        }
                    }
                }
                if (!$portalCategory) {
                    $portalCategory = new BtxCategory();
                    $portalCategory->entity_type = Smart::class;
                    $portalCategory->entity_id = $currentPortalSmart->id;
                    $portalCategory->parent_type = 'smart';
                }

                $portalCategory->group = $category['group'];
                $portalCategory->title = $category['title'];
                $portalCategory->name = $category['name'];
                $portalCategory->code = $category['code'];
                $portalCategory->type = $category['type'];
                $portalCategory->isActive = $category['isActive'];
                $portalCategory->bitrixId = $btxCategoryId;
                $portalCategory->bitrixCamelId = $btxCategoryId;
                $portalCategory->save();
                $portalCategoryId = $portalCategory->id;
                $portalSmartCategoryStages =  $portalCategory->stages->toArray();

                // Создаем или обновляем стадии
                $stages = InstallRPAController::setStages($hook, $category, $btxCategoryId, $portalSmartCategoryStages, $portalCategoryId);
                array_push($results, $stages);
            }
        }

        return $results;
    }


    static function setStages(
        $hook,
        $category,
        $btxCategoryId,
        $portalSmartCategoryStages,
        $portalCategoryId
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
            'filter' => ['ENTITY_ID' => 'DYNAMIC_' . $category['entityTypeId'] . '_STAGE_' . $btxCategoryId]
        ];

        $currentStagesResponse = Http::post($url, $hookCurrentStagesData);
        $currentStages = BitrixController::getBitrixResponse($currentStagesResponse, 'smart set stages');

        $resultStages = [];
        if (!empty($category['stages'])) {
            $stages = $category['stages'];
            foreach ($stages as $stage) {

                $statusId = 'DT' . $stage['entityTypeId'] . '_' . $btxCategoryId . ':' . $stage['bitrixId'];
                $dynamicId = 'DYNAMIC_' . $stage['entityTypeId'] . '_STAGE_' . $btxCategoryId;

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
                            'COLOR' => $stage['color'],
                            'isDefault' => $stage['isDefault'],
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
                            'COLOR' => $stage['color'],
                            'isDefault' => $stage['isDefault'],
                        ]
                    ];
                }



                $smartStageResponse = Http::post($url, $hookStagesDataCalls);
                $stageResultResponse = BitrixController::getBitrixResponse($smartStageResponse, 'stages install');

                $currentPortalStage = null;
                foreach ($portalSmartCategoryStages as $portalSmartCategoryStage) {

                    if (
                        $portalSmartCategoryStage['code'] === $stage['code'] ||
                        $portalSmartCategoryStage['bitrixId'] === $stage['bitrixId']
                    ) {
                        $currentPortalStage = BtxStage::find($portalSmartCategoryStage['id']);
                    }
                }
                if (!$currentPortalStage) {
                    $currentPortalStage = new BtxStage();
                    $currentPortalStage->btx_category_id = $portalCategoryId;
                }
                $currentPortalStage->title = $stage['title'];
                $currentPortalStage->name = $stage['name'];
                $currentPortalStage->code = $stage['code'];
                $currentPortalStage->color = $stage['color'];
                $currentPortalStage->bitrixId = $stage['bitrixId'];
                $currentPortalStage->isActive = $stage['isActive'];
                $currentPortalStage->save();

                array_push($resultStages, $stageResultResponse);
            }

            //deleting
            foreach ($currentStages as $index => $currentStage) {
                $delitingId = false;
                foreach ($stages as $stage) {
                    $statusId = 'DT' . $stage['entityTypeId'] . '_' . $btxCategoryId . ':' . $stage['bitrixId'];
                    if ($currentStage['STATUS_ID'] ===  $statusId) {
                        $delitingId =  $currentStage['ID'];
                    }
                    if ($delitingId) {
                        $methodStageDelete = '/crm.status.delete.json';
                        $url = $hook . $methodStageDelete;
                        $hookStagesDataCalls  =
                            [

                                'id' => $delitingId,
                                'params' => [
                                    'FORCED' => "Y"
                                ]

                            ];

                        $smartStageResponse = Http::post($url, $hookStagesDataCalls);
                        $stageResultResponse = BitrixController::getBitrixResponse($smartStageResponse, 'stages install delete');
                    }
                }
            }
        }

        return $resultStages;
    }
}
