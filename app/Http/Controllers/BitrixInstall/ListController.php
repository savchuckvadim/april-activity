<?php

namespace App\Http\Controllers\BitrixInstall;

use App\Http\Controllers\APIController;
use App\Http\Controllers\BitrixController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\PortalController;
use App\Models\Bitrixfield;
use App\Models\BitrixfieldItem;
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
            $currentBtxFieldId = null;
            $currentFieldCode = $listBtxCode . '_' . $gField['code'];
            $currentBtxFieldItems = [];
            $currentPortalFieldItems = [];



            $type = ListController::getFieldType($gField['type']);
            foreach ($currentPortalListFields as $index => $pField) {  //без portal нельзя взять и current btx

                if ($currentFieldCode === $pField->code) {

                    $currentPortalField =  $pField;

                    $currentBtxField = ListController::getListField($hook, $listBtxCode, 'PROPERTY_' . $pField->bitrixId, $type);
                    if (isset($currentBtxField['FIELD_ID'])) {
                        $currentBtxFieldId = $currentBtxField['FIELD_ID'];
                    }
                }
            }


            //создаем поле в btx

            $isMultiple = 'N';
            if ($gField['type'] == 'multiple') {
                $isMultiple = 'Y';
            }
            $listFieldSetData['FIELDS'] = [
                'NAME' => $gField['title'],
                'SORT' => $gField['order'],
                'MULTIPLE' => $isMultiple,
                'TYPE' => $type,

            ];
            if ($gField['type'] == 'enumeration') {
                $listValues = [];
                foreach ($gField['list'] as $index => $gItem) {
                    $listValues['n' . $gItem['SORT']] = [  // Используйте 'n' с добавлением индекса для ключей
                        'SORT' =>  $gItem['SORT'],
                        'VALUE' =>  $gItem['VALUE'],
                    ];
                }
                $listFieldSetData['FIELDS']['LIST'] = $listValues;
            }

            if ($currentBtxField && isset($currentPortalField['bitrixCamelId'])) {
                $method = '/lists.field.update';
                $listFieldSetData['FIELD_ID'] = $currentPortalField['bitrixCamelId'];
            } else {
                //создаем поле в btx
                $listFieldSetData['FIELDS']['CODE'] =  $currentFieldCode;
            }

            sleep(1);
            $url = $hook . $method;
            $setFieldResponse = Http::post($url, $listFieldSetData);
            $resultListFieldId = BitrixController::getBitrixResponse($setFieldResponse, 'SET List Field' . $method); //PROPERTY_313 | boolean


            if ($method === '/lists.field.add') {
                $currentBtxFieldId = $resultListFieldId;
            }


            if (!empty($currentBtxFieldId)) {

                $currentBtxField = ListController::getListField($hook, $listBtxCode, $currentBtxFieldId, $type);
            }


            



            if (!empty($currentBtxField) && isset($currentBtxField['ID'])) {
                if (!$currentPortalField) {          // если нет на портале такого - значит и btx тоже нет - потому что без portal data не будем знать id по которому находить field в btx
                    $currentPortalField = new Bitrixfield();
                    $currentPortalField->entity_id = $currentPortalList['id'];
                    $currentPortalField->entity_type = Bitrixlist::class;
                    $currentPortalField->parent_type = 'list';
                }
                $currentPortalField->title = $gField['title'];
                $currentPortalField->name = $gField['name'];
                $currentPortalField->code = $currentFieldCode;
                $currentPortalField->type = $gField['type'];
                $currentPortalField->bitrixId = $currentBtxField['ID'];
                $currentPortalField->bitrixCamelId = $currentBtxFieldId;
                $currentPortalField->save();
            }

            /// TODO SET ITEMS METHOD
            if ($gField['type'] == 'enumeration') {


                if (!empty($currentBtxField) && !empty($currentBtxField['DISPLAY_VALUES_FORM'])) {
                    $currentBtxFieldItems = $currentBtxField['DISPLAY_VALUES_FORM'];
                }

                if (!empty($currentPortalField) && !empty($currentPortalField->items)) {
                    $currentPortalFieldItems = $currentPortalField->item;
                }


                foreach ($gField['list'] as $gItem) {
                    $currentPItem = null;
                    $currentBtxItem = null;
                    // перебрать каждый эллемент списка из обновления
                    // определить текщий pItem по code
                    // по текущему pItem из его bitrixId найти текущий bitrix Item из списка "itemId": itemValue
                    // если нашел его - обновить если нет добавить в pushing items
                    // 
                   
                    //get cur btx and portal items from gItem
                    if (!empty($currentPortalFieldItems)) {
                        foreach ($currentPortalFieldItems as $btxId => $pItem) {
                            if ($pItem['code'] == $gItem['code']) {
                                $currentPItem = $pItem;
                            }
                        }
                    }
                    if (!empty($currentPItem)) {
                        if (!empty($currentBtxFieldItems)) {
                            foreach ($currentBtxFieldItems as $btxId => $value) {
                                if ($btxId == $currentPItem['bitrixId'] || $value == $currentPItem['title'] ||  $value == $gItem['VALUE']) {
                                    $currentBtxItem = ['bitrixId' => $btxId, 'value' => $value];
                                }
                            }
                        }
                    }


                    Log::channel('telegram')->error("gItem", [
                        'gItem' => $gItem,
                        'currentPItem' => $gItem,
                        'currentBtxItem' => $currentBtxItem,
    
    
                    ]);

                    
                    if (empty($currentPItem)) {
                        $currentPItem = new BitrixfieldItem();
                        $currentPItem->bitrixfield_id = $currentPortalField['id'];
                    }
                    if (!empty($currentBtxItem)) {
                        $currentPItem->bitrixId = $currentBtxItem['bitrixId'];
                        $currentPItem->name = $currentBtxItem['value'];
                        $currentPItem->title = $currentBtxItem['value'];
                    }else{
                        $currentPItem->bitrixId = 0;
                        $itemName = preg_replace('/[\x00-\x1F\x7F]/', '',  $gItem['VALUE']);
                        $currentPItem->name = $itemName;
                        $currentPItem->title = $itemName;


                    }
                  
                    
                 
                    $codeBitrixId = preg_replace('/[\x00-\x1F\x7F]/', '',  $gItem['code']);
                    $currentPItem->code = $codeBitrixId;
                    $currentPItem->save();
                  
                }
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

    public static function getListField($hook, $listBtxCode, $fieldId, $type)
    {

        $resultListField = null;
        $method = '/lists.field.get';

        $listFieldGetData = [
            'IBLOCK_TYPE_ID' => 'lists',
            'IBLOCK_CODE' => $listBtxCode,
            'FIELD_ID' =>   $fieldId  // 'PROPERTY_201'

        ];
        // Log::channel('telegram')->error("type data", [
        //     'listFieldGetData' => $listFieldGetData,
        //     'type' => $type,

        // ]);
        $url = $hook . $method;
        $getFieldResponse = Http::post($url, $listFieldGetData);
        $resultListField = BitrixController::getBitrixResponse($getFieldResponse, 'Get List Field' . $method);

        if (isset($resultListField[$type])) {
            $resultListField = $resultListField[$type];
        }

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
