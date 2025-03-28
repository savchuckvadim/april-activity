<?php

namespace App\Http\Controllers\AdminOuter\RPA;

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

class RPAController extends Controller
{
    public static function installRPA(
        Request $request
    ) {

        // $method = '/crm.deal.userfield.add';

        // Log::channel('telegram')->info('APRIL_ONLINE TEST', ['hook' => ['hook' => $hook]]);

        // $url = $hook . $method;
        //1) создает смарт процесс и сам задает  "entityTypeId" => 134,

        //3) записывает стадии и направления ввиде одного объекта json связь portal-smart

        // $initialData = Http::get(
        //     ''
        // );


        try {
            $data = $request->all();

            $domain = $data['domain'];
            $rpaData = $data['rpa'];

            $portal = Portal::where('domain', $domain)->first();
            $hook = BitrixController::getHook($domain);
            $portalId = null;
            $portalDeal = null;
            if ($portal && isset($portal->id)) {
                $portalId = $portal->id;
            }

            $fields = null;




            $rpa =    $rpaData;
            // Проверка на массив




            if (!empty($rpa)) {
                if (!empty($rpa['code'])) {
                    $currentPortalRPA = $portal->rpas()->where('code', $rpa['code'])->first();
                    // $smartEntityTypeId = $rpa['entityTypeId'];
                    if (!$currentPortalRPA) {

                        $currentPortalRPA = new BtxRpa();
                        $currentPortalRPA->portal_id = $portalId;
                    }
                    $currentPortalRPA->type = $rpa['type'];
                    $currentPortalRPA->code = $rpa['code'];
                    $currentPortalRPA->name = $rpa['name'];
                    $currentPortalRPA->title = $rpa['title'];
                    $currentPortalRPA->image = $rpa['image'];
                    $currentPortalRPA->description = $rpa['bitrixId'];
                    $currentPortalRPA->typeId = $rpa['bitrixId'];

                    $currentPortalRPA->bitrixId = $rpa['bitrixId'];
                    $currentPortalRPA->entityTypeId =  $rpa['bitrixId'];
                    $currentPortalRPA->forStageId = $rpa['bitrixId'];

                    $currentPortalRPA->forFilterId =  $rpa['bitrixId'];
                    $currentPortalRPA->crmId = $rpa['bitrixId'];

                    $currentPortalRPA->save();



                    Log::channel('telegram')->info('RPA ONLINE ADMIN', [
                        'currentPortalRPA' => $currentPortalRPA
                    ]);

                    Log::info('RPA ONLINE ADMIN', [
                        'currentPortalRPA' => $currentPortalRPA
                    ]);
                }
            }




            $categories = RPAController::setCategories($hook, $rpa['categories'], $rpa, $currentPortalRPA);
            // array_push($resultSmarts, $currentBtxSmart);

            // $fields = RPAFieldsController::setFields('rpa', $domain, $rpa, $currentPortalRPA);

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

        return APIController::getSuccess(['fields' => $fields, 'categories' => $categories]);
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
        $categories,  //from google
        $rpa,
        $currentPortalRPA
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

        $btxCategoryId = 0; //потому что у rpa нет категорий в битриксе

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



        $currentPortalCategories = $currentPortalRPA->categories;


        // $methodCategoryList = '/crm.category.list.json';
        // $url = $hook . $methodCategoryList;


        // }
        $defaultCategoryId = null;
        $results = [];

        foreach ($categories as $category) {     //from google
            $categoryName = $category['name'];
            // $isDefault = $category['type'] === 'base' ? 'Y' : 'N';

            // Ищем, есть ли уже категория по умолчанию

            // $methodCategoryInstall = '/crm.category.add.json';
            // $urlInstall = $hook . $methodCategoryInstall;
            // $btxCategoryId = null;


            // $hookCategoriesData = [
            //     'entityTypeId' => $category['entityTypeId'],
            //     // 'statusEntityId' => 'DEAL_STAGE_3',
            //     'fields' => [
            //         'name' => $categoryName,
            //         'title' => $category['title'],
            //         'isDefault' => $category['isDefault'],

            //         'sort' => $category['order'],
            //         'code' => $category['code']
            //     ]
            // ];



            // $smartCategoriesResponse = Http::post($urlInstall, $hookCategoriesData);
            // $bitrixResponseCategory = BitrixController::getBitrixResponse($smartCategoriesResponse, 'category');

            // // if (isset($bitrixResponseCategory['id'])) {
            // //     $categoryId = $bitrixResponseCategory['id'];
            // // }
            // if (!empty($bitrixResponseCategory['category'])) {
            //     $bitrixResponseCategory = $bitrixResponseCategory['category'];
            //     if (isset($bitrixResponseCategory['id'])) {
            //         $btxCategoryId = $bitrixResponseCategory['id'];
            //     }
            // }

            // if ($btxCategoryId) {

            //обновляем категорию в БД
            $portalCategory = null;
            $currentPortalCategory =  null;

            if (!empty($currentPortalCategories)) {

                // $currentPortalCategory =   $currentPortalCategories[0];
                foreach ($currentPortalCategories as $pRpaCategory) {
                    if (!empty($pRpaCategory) && isset($pRpaCategory['code'])) {
                        if ($pRpaCategory['code'] == $category['code']) {
    
                            $portalCategory = BtxCategory::find($pRpaCategory['id']);
                        }
                    }
                }

                
            }
            if (empty($portalCategory)) {
                $portalCategory = new BtxCategory();
                $portalCategory->entity_type = BtxRpa::class;
                $portalCategory->entity_id = $currentPortalRPA->id;
                $portalCategory->parent_type = 'rpa';
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
            $stages = RPAController::setStages($hook, $category, $btxCategoryId, $portalSmartCategoryStages, $portalCategoryId, $currentPortalRPA['bitrixId']);
            array_push($results, $stages);
            // }
        }

        return $results;
    }


    static function setStages(
        $hook,
        $category,
        $btxCategoryId,
        $portalSmartCategoryStages,
        $portalCategoryId,
        $currentRPABxId,
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


        Log::channel('telegram')->info(
            'INFO',
            ['typeId' => $currentRPABxId]
        );

        Log::channel('telegram')->info(
            'INFO',
            ['portalSmartCategoryStages' => $portalSmartCategoryStages]
        );

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



        // $currentstagesMethod = '/rpa.stage.listForType.json';
        // $url = $hook . $currentstagesMethod;
        // $hookCurrentStagesData = [
        //     'filter' => ['typeId' => $currentRPABxId]
        // ];

        // $hookCurrentStagesData = ['typeId' => $currentRPABxId];


        // $currentStagesResponse = Http::post($url, $hookCurrentStagesData);
        // $currentStages = BitrixController::getBitrixResponse($currentStagesResponse, 'rpa set stages');

        $resultStages = [];
        if (!empty($category['stages'])) {
            $stages = $category['stages'];
            foreach ($stages as $stage) {  //from google

                $statusId = $stage['code'];

                $isExist = false;
                // if (!empty($currentStages) && is_array($currentStages)) {
                //     foreach ($currentStages as $currentStage) {
                //         if (isset($currentStage['code'])) {
                //             if ($currentStage['code'] === $statusId) {
                //                 $isExist = $currentStage['id'];
                //             }
                //         }

                //         if (isset($currentStage['CODE'])) {
                //             if ($currentStage['CODE'] === $statusId) {
                //                 $isExist = $currentStage['ID'];
                //             }
                //         }
                //     }
                // }

                // Log::channel('telegram')->info(
                //     'INFO',
                //     [
                //         'isExist' => $isExist,
                //         'typeId' => $currentRPABxId,

                //     ]
                // );


                // if ($isExist) {
                //     // Update stage
                //     $methodStageInstall = '/rpa.stage.update.json';
                //     $url = $hook . $methodStageInstall;
                //     $hookStagesDataCalls = [
                //         'id' => $isExist,
                //         // 'typeId' => $currentRPABxId,
                //         'fields' => [


                //             'name' => $stage['title'],
                //             'code' => $stage['code'],
                //             'sort' => $stage['order'],
                //             'color' => $stage['color'],
                //             'semantic' => $stage['semantic'],
                //             'isFirst' => $stage['isFirst'],
                //             'isSuccess' => $stage['isSuccess'],
                //             'isFail' => $stage['isFail'],
                //         ]
                //     ];
                // } else {
                //     // Create stage
                //     $methodStageInstall = '/rpa.stage.add.json';
                //     $url = $hook . $methodStageInstall;
                //     $hookStagesDataCalls = [
                //         // 'statusId' => $statusId,

                //         'fields' => [
                //             'typeId' => $currentRPABxId,
                //             'name' => $stage['title'],
                //             'code' => $stage['code'],
                //             'sort' => $stage['order'],
                //             'color' => $stage['color'],
                //             'semantic' => $stage['semantic'],
                //             'isFirst' => $stage['isFirst'],
                //             'isSuccess' => $stage['isSuccess'],
                //             'isFail' => $stage['isFail'],
                //         ]
                //     ];
                // }



                // $smartStageResponse = Http::post($url, $hookStagesDataCalls);
                // $stageResultResponse = BitrixController::getBitrixResponse($smartStageResponse, 'stages install');

                $currentPortalStage = null;
                foreach ($portalSmartCategoryStages as $portalSmartCategoryStage) {

                    if (
                        $portalSmartCategoryStage['code'] === $stage['code']
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

                array_push($resultStages, $currentPortalStage);
            }

            //deleting
            // foreach ($currentStages as $index => $currentStage) {
            //     $delitingId = false;
            //     foreach ($stages as $stage) {
            //         $statusId = 'DT' . $stage['entityTypeId'] . '_' . $btxCategoryId . ':' . $stage['bitrixId'];
            //         if ($currentStage['STATUS_ID'] ===  $statusId) {
            //             $delitingId =  $currentStage['ID'];
            //         }
            //         if ($delitingId) {
            //             $methodStageDelete = '/crm.status.delete.json';
            //             $url = $hook . $methodStageDelete;
            //             $hookStagesDataCalls  =
            //                 [

            //                     'id' => $delitingId,
            //                     'params' => [
            //                         'FORCED' => "Y"
            //                     ]

            //                 ];

            //             $smartStageResponse = Http::post($url, $hookStagesDataCalls);
            //             $stageResultResponse = BitrixController::getBitrixResponse($smartStageResponse, 'stages install delete');
            //         }
            //     }
            // }
        }

        return $resultStages;
    }
}
