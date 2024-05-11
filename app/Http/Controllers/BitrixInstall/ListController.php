<?php

namespace App\Http\Controllers\BitrixInstall;

use App\Http\Controllers\APIController;
use App\Http\Controllers\BitrixController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\PortalController;
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
        $listBtxCode = $currentGoogleList['group'] . '_' . $currentGoogleList['code'];

        $currentBtxList  = ListController::getList($hook, $listBtxCode);
        if (is_array($currentBtxList) && !empty($currentBtxList)) {
            $currentBtxList = $currentBtxList[0];
        }

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


        if ($currentBtxList) {
            $method = '/lists.update';
        }
        $url = $hook . $method;
        $createListResponse = Http::post($url, $btxListSetData);
        $resultListId = BitrixController::getBitrixResponse($createListResponse, 'Create List - createListResponse');
        $resultList = null;

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
        }
    }

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
        Log::channel('telegram')->info('APRIL_ONLINE', [
            'get resultList'   => $resultList

        ]);
        return  $resultList;
    }
}
