<?php

namespace App\Http\Controllers\AdminOuter;

use App\Http\Controllers\APIController;
use App\Http\Controllers\BitrixController;
use App\Http\Controllers\Controller;

use App\Models\BtxCategory;
use App\Models\BtxDeal;
use App\Models\BtxStage;
use App\Models\Portal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DealController extends Controller
{
    public static function setDeals(Request $request)
    {




        try {
            $data = $request->all();

            $domain = $data['domain'];
            $dealData = $data['deal'];

            $portal = Portal::where('domain', $domain)->first();
            $portalId = null;
            $portalDeal = null;
            if ($portal && isset($portal->id)) {
                $portalId = $portal->id;
            }





            $portalDeal = null;

            $portalDealCategories = [];

            if ($portal) {
                if (!empty($portal->deals->first())) { // Сделка у портала на DB существует
                    $portalDeals = $portal->deals;
                    $portalDeal = $portalDeals->first();

                    if (!empty($portalDeal) && isset($portalDeal->id)) {
                        $portalDealId = $portalDeal->id;
                        $portalDealCategories = $portalDeal->categories;
                    }
                } else {
                    //если сделки у портала не существует - создать
                    $portalDeal = new BtxDeal();
                    $portalDeal->name = 'Сделка ' . $domain;
                    $portalDeal->title = 'Сделка ' . $domain;
                    $portalDeal->code = 'deal';
                    $portalDeal->portal_id = $portalId;
                    $portalDeal->save();
                }
            }


            Log::channel('telegram')->error("install test", ['portalDeal' => $portalDeal]);
            Log::channel('telegram')->error("install test", ['portalDealCategories' => $portalDealCategories]);


            $categories = DealController::setCategories($dealData['categories'], $portalDealCategories, $portalDeal);
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
        $categories,
        $portalDealCategories,
        $portalDeal
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


        // $methodCategoryList = '/crm.category.list.json';
        // $url = $hook . $methodCategoryList;

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
        $portalCategoryId = null;
        $portalDealId = $portalDeal->id;
        foreach ($categories as $category) {
            if ($category['isNeedUpdate']) {



                $categoryName = $category['name'];
                $isDefault = $category['isDefault'];
                $categoryId = $category['id'];
                // Ищем, есть ли уже категория по умолчанию
                // $existingDefaultCategory = null;
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


                // if ($categoryId !== null) {
                //     $hookCategoriesData['id'] = $categoryId;
                // }


                if ($categoryId) {

                    //обновляем категорию в БД
                    $portalCategory = null;
                    foreach ($portalDealCategories as $portalDealCategory) { //перебираем категории сделки привязанной к порталу db
                        if (!empty($portalDealCategory) && isset($portalDealCategory['code'])) {
                            if ($portalDealCategory['code'] == $category['code']) {

                                $portalCategory = BtxCategory::find($portalDealCategory['id']);
                            }
                        }
                    }
                    if (empty($portalCategory)) {
                        $portalCategory = new BtxCategory();
                        $portalCategory->entity_type = BtxDeal::class;
                        $portalCategory->entity_id = $portalDealId;
                        $portalCategory->parent_type = 'deal';
                    }
                    Log::channel('telegram')->info("categoryId", [
                        'portalCategory' => $portalCategory,

                    ]);
                    $portalCategory->group = $category['group'];
                    $portalCategory->title = $category['title'];
                    $portalCategory->name = $category['name'];
                    $portalCategory->code = $category['code'];
                    $portalCategory->type = $category['type'];
                    $portalCategory->isActive = $category['isActive'];
                    $portalCategory->bitrixId = $categoryId;
                    $portalCategory->bitrixCamelId = $categoryId;
                    $portalCategory->save();
                    $portalCategoryId = $portalCategory->id;
                    $portalDealCategoryStages =  $portalCategory->stages->toArray();



                    Log::channel('telegram')->info("categoryId", [
                        'portalCategory' => $portalCategory,
                        'portalDealCategoryStages' => $portalDealCategoryStages,

                    ]);
                    // Создаем или обновляем стадии
                    $stages = DealController::setStages($category, $categoryId, $portalDealCategoryStages, $portalCategoryId);
                    array_push($results, $stages);
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

                // Log::channel('telegram')->info("categoryId", [
                //     'categoryId' => $categoryId,

                // ]);
                // // Создаем или обновляем стадии
                // $stages = InstallDealController::setStages($hook, $category, $categoryId, $portalDealCategoryStages);
                // array_push($results, $stages);
            }
        }

        return $results;
    }


    static function setStages(
        $category,
        $categoryId,

        $portalDealCategoryStages,
        $portalCategoryId,
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




        $resultStages = [];
        if (!empty($category['stages'])) {
            $stages = $category['stages'];
            foreach ($stages as $stage) {
                $currentPortalStage = null;

                foreach ($portalDealCategoryStages as $portalDealCategoryStage) {

                    if (
                        $portalDealCategoryStage['code'] === $stage['code'] ||
                        $portalDealCategoryStage['bitrixId'] === $stage['bitrixId']
                    ) {
                        $currentPortalStage = BtxStage::find($portalDealCategoryStage['id']);
                    }
                }
                if (empty($currentPortalStage)) {
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
                // $bitrixResponseStage = $smartStageResponse->json();
                Log::info('SUCCESS SMART INSTALL', ['currentPortalStage' => $currentPortalStage]);
            }



            return $resultStages;
        }
    }
}
