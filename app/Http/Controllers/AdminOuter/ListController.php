<?php

namespace App\Http\Controllers\AdminOuter;

use App\Http\Controllers\APIController;
use App\Http\Controllers\BitrixController;
use App\Http\Controllers\Controller;
use App\Models\Bitrixfield;
use App\Models\BitrixfieldItem;
use App\Models\Bitrixlist;
use App\Models\Portal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ListController extends Controller
{
    public static function setLists(
        Request $request


    ) {
        $results = [];
        $data = $request->all();
        $domain = '';
        try {

            // $data = $requ['result'];
            $domain = $data['domain'];
            $lists = $data['lists'];

            // $jsonFilePath = storage_path('app/public/install/save_return.json');

            // Чтение данных из файла
            // $jsonData = file_get_contents($jsonFilePath);
            // Преобразование JSON в объект (можно указать true, чтобы получить массив)
            // $data = json_decode($jsonData);
            // Преобразование JSON в массив
            // $list = $data->result->list; // Если нужен массив: $list = $data['result']['list'];
            if (!empty($lists)) {
                foreach ($lists as $list) {


                    # code...

                    // $domain = $data->result->domain; // Если нужен массив: $list = $data['result']['list'];




                    $hook = BitrixController::getHook($domain);
                    $portal = Portal::where('domain', $domain)->first();
                    $webhookRestKey = $portal->getHook();
                    $hook = 'https://' . $domain . '/' . $webhookRestKey;
                    $portalId = $portal['id'];
                    // $group = 'sales';

                    $portalLists = $portal->lists;
                    $currentPortalList = null;

                    if (!empty($portalLists) && count($portalLists) > 0) {
                        $currentPortalList = $portalLists
                            ->where('group', $list['group'])
                            ->where('type', $list['code'])->first();
                    }

                    // return APIController::getSuccess([
                    //     'portalLists' => $portalLists,
                    //     'domain' => $domain,
                    //     'portal' => $portal,
                    //     'currentPortalList' => $currentPortalList,
                    // ]);

                    $result = ListController::setList($hook, $list, $currentPortalList, $portalId);
                    // array_push($result, $results);
                }
            }
            $portal = Portal::where('domain', $domain)->first();
            if (!empty($portal)) {

                $portalLists = $portal->lists;
                return APIController::getSuccess(['portal_lists' => $portalLists]);
            } else {
                return APIController::getSuccess(['portal' => null]);
            }
        } catch (\Exception $e) {
            Log::error('Error in install', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return APIController::getError('An error occurred during installation', [
                'domain' => $domain,
                'comedata' => $data,

                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }
    }

    public static function setList($hook, $list, $currentPortalList, $portalId)
    {


        // сначала обновляем или создаем на битриксе чтобы получить id
        // затем обновляем в портал или создаем и записываем туда id
        // $method = '/lists.add';
        // $currentGoogleFields = $currentGoogleList['fields'];
        // $resultList = null;
        // $listBtxCode = $currentGoogleList['group'] . '_' . $currentGoogleList['code'];

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
        //     $method = '/lists.update';
        //     $btxListSetData = [
        //         'IBLOCK_TYPE_ID' => 'lists',
        //         // 'IBLOCK_CODE' => $listBtxCode,
        //         'IBLOCK_ID' => $currentBtxList['ID'],

        //         'FIELDS' => $listData
        //     ];
        // }
        // $url = $hook . $method;
        // $createListResponse = Http::post($url, $btxListSetData);
        // $resultListId = BitrixController::getBitrixResponse($createListResponse, 'Create List ' . $method);


        // if (!empty($resultListId)) {

        //     $resultList = ListController::getList($hook, $listBtxCode);
        // }

        if (!empty($list) && !empty($list['bitrixId'])) {
            if (!$currentPortalList) {
                $currentPortalList = new Bitrixlist();
                $currentPortalList->portal_id = $portalId;
                $currentPortalList->group = $list['group'];
                $currentPortalList->type = $list['code'];
            }
            $currentPortalList->name = $list['title'];
            $currentPortalList->title = $list['title'];
            $currentPortalList->bitrixId = $list['bitrixId'];
            $currentPortalList->save();
        }

        //install or update fields
        return ListController::setListFields($list, $list['fields'], $currentPortalList, $portalId);
    }



    public static function setListFields($list, $listFields, $currentPortalList, $portalId)
    {


        // сначала обновляем или создаем на битриксе чтобы получить id
        // затем обновляем в портал или создаем и записываем туда id
        // $method = '/lists.field.add';
        // $resultListBtxFields = [];
        // $listFieldSetData = [
        //     'IBLOCK_TYPE_ID' => 'lists',
        //     'IBLOCK_CODE' => $listBtxCode,


        // ];

        // $currentPortalListFields = $currentPortalList->fields;


        foreach ($listFields as $gField) {
            $currentBtxField = $gField;  //объект field  сданными из bx+googleSheet
            $currentBtxFieldId = null;
            $currentFieldCode = $list['group'] . '_' . $list['code'] . '_' . $gField['code'];
            $currentBtxFieldItems = [];
            $currentPortalFieldItems = [];

            $currentPortalField = $currentPortalList->fields->where('code', $currentFieldCode)->first();


            // Log::channel('telegram')->info(
            //     'tst currentPortalField',
            //     [
            //         'currentPortalField' => $currentPortalField
            //     ]
            // );

            if (!empty($currentBtxField) && isset($currentBtxField['bitrixId'])) {
                if (!$currentPortalField) {          // если нет на портале такого - значит и btx тоже нет - потому что без portal data не будем знать id по которому находить field в btx
                    $currentPortalField = new Bitrixfield();
                    $currentPortalField->entity_id = $currentPortalList['id'];
                    $currentPortalField->entity_type = Bitrixlist::class;
                    $currentPortalField->parent_type = 'list';
                }
                
                $bitrixCamelId = 'PROPERTY_' . $gField['bitrixId'];

                $currentPortalField->title = $gField['title'];
                $currentPortalField->name = $gField['name'];
                $currentPortalField->code = $currentFieldCode;
                $currentPortalField->type = $gField['type'];
                $currentPortalField->bitrixId = $gField['bitrixId'];
                $currentPortalField->bitrixCamelId = $bitrixCamelId;
                $currentPortalField->save();
            }

            /// TODO SET ITEMS METHOD
            if ($gField['type'] == 'enumeration') {


                // if (!empty($currentBtxField) && !empty($currentBtxField['DISPLAY_VALUES_FORM'])) {
                //     $currentBtxFieldItems = $currentBtxField['DISPLAY_VALUES_FORM'];
                // }

                if (!empty($currentPortalField) && !empty($currentPortalField['items'])) {
                    $currentPortalFieldItems = $currentPortalField['items'];
                }


                foreach ($gField['list'] as $gItem) {
                    $currentPItem = null;
                    // $currentBtxItem = null;
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
                    // print_r('$currentPortalField->id');
                    // print_r($currentPortalField['id']);


                    if (empty($currentPItem)) {
                        $currentPItem = new BitrixfieldItem();
                        $currentPItem->bitrixfield_id = $currentPortalField['id'];
                    }
                    $currentPItem->bitrixId = $gItem['id'];

                    $itemName = preg_replace('/[\x00-\x1F\x7F]/', '',  $gItem['value']);
                    $currentPItem->name = $itemName;
                    $currentPItem->title = $itemName;
                    $codeBitrixId = preg_replace('/[\x00-\x1F\x7F]/', '',  $gItem['code']);
                    $currentPItem->code = $codeBitrixId;
                    $currentPItem->save();
                    // print_r($currentPItem['id']);
                }
            }
        }
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
