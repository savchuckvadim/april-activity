<?php

namespace App\Http\Controllers\BitrixInstall;

use App\Http\Controllers\APIController;
use App\Http\Controllers\BitrixController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\PortalController;
use App\Models\Bitrixfield;
use App\Models\Bitrixlist;
use App\Models\Portal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ListController extends Controller
{
    public static function setLists($token)
    {

        try {
            $domain = 'april-dev.bitrix24.ru';
            $hook = BitrixController::getHook($domain);
            $portal = Portal::where('domain', $domain)->first();
            $webhookRestKey = $portal->getHook();
            $hook = 'https://' . $domain . '/' . $webhookRestKey;
            $portalId = $portal['id'];
            $group = 'sales';

            $portalLists = $portal->lists;

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




            // Проверка на массив
            if (is_array($googleData) && !empty($googleData['lists'])) {
                $lists = $googleData['lists'];

                foreach ($lists as $list) {
                    $currentPortalList = null;
                    $currentBtxlList = null;
                    $currentBtxSmartId = null;


                    foreach ($list['fields'] as $field) {
                        if (!empty($field['list'])) {
                            foreach ($field['list'] as $fieldItem) {
                            }
                        }
                    }

                    //portal list
                    $currentPortalList = $portalLists
                        ->where('group', $list['group'])
                        ->where('type', $list['code'])->first();

                    ListController::setList($hook, $list, $currentPortalList, $portalId);
                }
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
    }

    public static function setList($hook, $currentGoogleList, $currentPortalList, $portalId)
    {


        // сначала обновляем или создаем на битриксе чтобы получить id
        // затем обновляем в портал или создаем и записываем туда id
        $method = '/lists.add';
        $currentGoogleFields = $currentGoogleList['fields'];
        $resultList = null;
        $listBtxCode = $currentGoogleList['group'] . '_' . $currentGoogleList['code'];

        $currentBtxList  = ListController::getList($hook, $listBtxCode);

        $listData = [
            'NAME' => $currentGoogleList['title'],
            // 'DESCRIPTION' => '',
            'SORT' =>  $currentGoogleList['order'],
            // 'PICTURE' => document.getElementById('iblock-image-add'),
            'BIZPROC' => 'Y'
        ];
        // bitrix list
        $btxListSetData = [
            'IBLOCK_TYPE_ID' => 'lists',
            'IBLOCK_CODE' => $listBtxCode,
            'FIELDS' => $listData
        ];


        if ($currentBtxList && isset($currentBtxList['ID'])) {
            $method = '/lists.update';
            $btxListSetData = [
                'IBLOCK_TYPE_ID' => 'lists',
                // 'IBLOCK_CODE' => $listBtxCode,
                'IBLOCK_ID' => $currentBtxList['ID'],

                'FIELDS' => $listData
            ];
        }
        $url = $hook . $method;
        $createListResponse = Http::post($url, $btxListSetData);
        $resultListId = BitrixController::getBitrixResponse($createListResponse, 'Create List ' . $method);


        if (!empty($resultListId)) {

            $resultList = ListController::getList($hook, $listBtxCode);
        }

        if ($resultList && !empty($resultList['ID'])) {
            if (!$currentPortalList) {
                $currentPortalList = new Bitrixlist();
                $currentPortalList->portal_id = $portalId;
                $currentPortalList->group = $currentGoogleList['group'];
                $currentPortalList->type = $currentGoogleList['code'];
            }
            $currentPortalList->name = $currentGoogleList['title'];
            $currentPortalList->title = $currentGoogleList['title'];
            $currentPortalList->bitrixId = $resultList['ID'];
            $currentPortalList->save();
        }

        //install or update fields
        ListController::setListFields($hook, $listBtxCode, $currentGoogleFields, $currentPortalList, $portalId);
    }



    public static function setListFields($hook, $listBtxCode, $currentGoogleFields, $currentPortalList, $portalId)
    {


        // сначала обновляем или создаем на битриксе чтобы получить id
        // затем обновляем в портал или создаем и записываем туда id
        $method = '/lists.field.add';
        $resultListBtxFields = [];
        $listFieldSetData = [
            'IBLOCK_TYPE_ID' => 'lists',
            'IBLOCK_CODE' => $listBtxCode,


        ];

        $currentPortalListFields = $currentPortalList->fields;


        foreach ($currentGoogleFields as $gField) {
            $currentPortalField = null;
            $currentBtxField = null;


            foreach ($currentPortalListFields as $pField) {

                if ($gField['code'] === $pField['code']) {
                    $currentPortalField =  $pField;
                    $currentBtxField = ListController::getListField($hook, $listBtxCode, $pField['bitrixId']);
                }
            }


            //создаем поле в btx
            $type = ListController::getFieldType($gField['type']);
            $isMultiple = false;
            if ($gField['type'] == 'multiple') {
                $isMultiple = true;
            }
            $listFieldSetData['FIELDS'] = [
                'NAME' => $gField['title'],
                'SORT' => $gField['order'],
                'MULTIPLE' => $isMultiple,
                'TYPE' => $type,

            ];
            if ($gField['type'] == 'enumeration') {
                $listFieldSetData['FIELDS']['LIST'] = $gField['list'];
            }

            if ($currentBtxField) {
                $method = '/lists.field.update';
            } else {
                //создаем поле в btx
                $listFieldSetData['FIELDS']['CODE'] =  $listBtxCode . '_' . $gField['code'];
            }
            $url = $hook . $method;
            $setFieldResponse = Http::post($url, $listFieldSetData);
            $resultListFieldId = BitrixController::getBitrixResponse($setFieldResponse, 'SET List Field' . $method); //PROPERTY_313

            if (!empty($resultListFieldId)) {

                $currentBtxField = ListController::getListField($hook, $listBtxCode, $resultListFieldId);
            }

            if ($gField['type'] == 'enumeration') {
                Log::channel('telegram')->error("set List Field", [
                    'result Field currentBtxField' => $currentBtxField,


                ]);
                if(isset($currentBtxField['list'])){
                    Log::channel('telegram')->error("set List Field", [
                        'result Field list' => $currentBtxField['list'],
    
    
                    ]);

                }

                if(isset($currentBtxField['LIST'])){
                    Log::channel('telegram')->error("set List Field", [
                        'result Field LIST' => $currentBtxField['LIST'],
    
    
                    ]);

                }
            }


            if (!empty($currentBtxField)) {
                if (!$currentPortalField) {          // если нет на портале такого - значит и btx тоже нет - потому что без portal data не будем знать id по которому находить field в btx
                    $currentPortalField = new Bitrixfield();
                    $currentPortalField->entity_id = $currentPortalList['id'];
                    $currentPortalField->entity_type = Bitrixlist::class;
                    $currentPortalField->parent_type = 'list';
                }
                $currentPortalField->title = $gField['title'];
                $currentPortalField->name = $gField['name'];
                $currentPortalField->code = $listBtxCode . '_' . $gField['code'];
                $currentPortalField->type = $gField['type'];
                $currentPortalField->bitrixId = $resultListFieldId;
                $currentPortalField->bitrixCamelId = $resultListFieldId;
                $currentPortalField->save();
            }
        }

        // $resultListBtxFields = ListController::getListField($hook, $listBtxCode, 'PROPERTY_201');
        // Log::channel('telegram')->error("setListFields ", [
        //     'resultListBtxFields' => $resultListBtxFields,


        // ]);
        // $currentBtxList  = ListController::getList($hook, $listBtxCode);

        // $listData = [
        //     'NAME' => $currentGoogleList['title'],
        //     // 'DESCRIPTION' => '',
        //     'SORT' =>  $currentGoogleList['order'],
        //     // 'PICTURE' => document.getElementById('iblock-image-add'),
        //     'BIZPROC' => 'Y'
        // ];
        // // bitrix list
        // $btxListSetData = [
        //     'IBLOCK_TYPE_ID' => 'lists',
        //     'IBLOCK_CODE' => $listBtxCode,
        //     'FIELDS' => $listData
        // ];


        // if ($currentBtxList && isset($currentBtxList['ID'])) {
        // }
    }




    //utils

    public static function getList($hook, $listCode)
    {

        $resultList = null;
        $methodGet = '/lists.get';
        $urlGet = $hook . $methodGet;
        $btxListGetData = [
            'IBLOCK_TYPE_ID' => 'lists',
            'IBLOCK_CODE' => $listCode,

        ];

        $getCreatedListResponse = Http::post($urlGet, $btxListGetData);
        $resultList = BitrixController::getBitrixResponse($getCreatedListResponse, 'Create List - get created');

        if (is_array($resultList) && !empty($resultList)) {
            $resultList = $resultList[0];
        }

        return  $resultList;
    }

    public static function getListField($hook, $listBtxCode, $fieldId)
    {

        $resultListField = null;
        $method = '/lists.field.get';

        $listFieldGetData = [
            'IBLOCK_TYPE_ID' => 'lists',
            'IBLOCK_CODE' => $listBtxCode,
            // 'FIELD_ID' =>   $fieldId  // 'PROPERTY_201'

        ];

        $url = $hook . $method;
        $getFieldResponse = Http::post($url, $listFieldGetData);
        $resultListField = BitrixController::getBitrixResponse($getFieldResponse, 'Get List Field' . $method);
        // if (is_array($resultListField) && !empty($resultListField)) {
        //     $resultListField = $resultListField[0];
        // }
        // Log::channel('telegram')->error("getListField", [
        //     'resultListField' => $resultListField,


        // ]);
        return  $resultListField;
    }

    public static function getFieldType($type)
    {

        //         TYPE тип (обязательно)
        // S - Строка
        // N - Число
        // L - Список
        // F - Файл
        // G - Привязка к разделам
        // E - Привязка к элементам
        // S:Date - Дата
        // S:DateTime - Дата/Время
        // S:HTML - HTML/текст
        // E:EList - Привязка к элементам в виде списка. При создании поля с этим типом необходимо обязательно указать LINK_IBLOCK_ID id информационного блока, элементы которого будут отображаться в селекторе этого поля.
        // N:Sequence - Счетчик
        // S:Money - Деньги
        // S:DiskFile - Файл (Диск)
        // S:map_yandex - Привязка к Яндекс.Карте
        // S:employee - Привязка к сотруднику
        // S:ECrm - Привязка к элементам CRM
        $resultType = 'S';
        switch ($type) {
            case 'string':
            case 'multiple':
                $resultType = 'S';
                break;
            case 'datetime':
                $resultType = 'S:DateTime';
                break;
            case 'employee':
                $resultType = 'S:employee';
                break;

            case 'enumeration':
                $resultType = 'L';
                break;
            case 'crm':
                $resultType = 'S:ECrm';
                break;
            default:
                $resultType = 'S';
                break;
        }
        return $resultType;
    }
}
