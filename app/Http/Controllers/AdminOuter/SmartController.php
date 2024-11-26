<?php

namespace App\Http\Controllers\AdminOuter;

use App\Http\Controllers\AdminOuter\FieldsController;
use App\Http\Controllers\APIController;
use App\Http\Controllers\BitrixController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\PortalController;
use App\Models\BtxCategory;
use App\Models\BtxStage;
use App\Models\Portal;
use App\Models\Smart;
use FontLib\Table\Type\name;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmartController extends Controller
{
    public static function install(
        Request $request
    ) {


        try {
            $currentPortalSmart = null;
            $result = [];
            $categories = null;
            $data = $request->all();

            $domain = $data['domain'];
            $smarts = $data['smarts'];
            $portal = Portal::where('domain', $domain)->first();
            $portalId = null;

            if ($portal && isset($portal->id)) {
                $portalId = $portal->id;
            }

            foreach ($smarts as $smart) {
                $currentPortalSmart = null;
                $currentBtxSmart = null;

                $currentPortalSmart = $portal->smarts()
                    ->where('type', $smart['type'])
                    ->where('code', $smart['code'])
                    ->first();



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
                $currentPortalSmart->crmId = $smart['bitrixId'];
                $currentPortalSmart->crm = $smart['crm'] . '_';
                $currentPortalSmart->save();

                array_push($result, $currentPortalSmart);

                $categories = SmartController::setCategories($smart['categories'], $currentBtxSmart, $currentPortalSmart);
                array_push($resultSmarts, $currentBtxSmart);

                // if (!empty($smart['fields'])) {
                //     FieldsController::setSmartFields($domain,  $currentPortalSmart, $smart['fields']);
                // }
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
        $portal = Portal::where('domain', $domain)->first();
        return APIController::getSuccess(['smart' => $result, 'portal' => $portal, 'categories' => $categories]);
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

        //обновляем категорию в БД


        $currentPortalCategories = $currentPortalSmart->categories->toArray();
        foreach ($categories as $category) {

            $portalCategory = null;
            foreach ($currentPortalCategories as $currentPortalCategory) { //перебираем категории сделки привязанной к порталу db
                if (!empty($currentPortalCategory) && isset($currentPortalCategory['code'])) {
                    if ($currentPortalCategory['code'] == $category['code']) {

                        $portalCategory = BtxCategory::find($currentPortalCategory['id']);
                    }
                }
            }
            if (empty($portalCategory)) {
                $portalCategory = new BtxCategory();
                $portalCategory->entity_type = Smart::class;
                $portalCategory->entity_id = $currentPortalSmart->id;
                $portalCategory->parent_type = 'smart';
            }
            $btxCategoryId = 'DT' . $category['entityTypeId'] . '_' . $category['bitrixId'];
            $portalCategory->group = $category['group'];
            $portalCategory->title = $category['title'];
            $portalCategory->name = $category['name'];
            $portalCategory->code = $category['code'];
            $portalCategory->type = $category['type'];
            $portalCategory->isActive = $category['isActive'];
            $portalCategory->bitrixId =  $category['bitrixId'];
            $portalCategory->bitrixCamelId = $btxCategoryId;
            $portalCategory->save();
            $portalCategoryId = $portalCategory->id;
            $portalSmartCategoryStages =  $portalCategory->stages->toArray();

            // Создаем или обновляем стадии
            $stages = SmartController::setStages($category, $category['bitrixId'], $portalSmartCategoryStages, $portalCategoryId);
            array_push($results,['category' => $portalCategory, 'stages' => $stages]);
            # code...
        }
        return $results;
    }


    static function setStages(

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


        $stages = $category['stages'];
       

        foreach ($stages  as $stage) {
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

            # code...
        }


        return $resultStages;
    }
}
