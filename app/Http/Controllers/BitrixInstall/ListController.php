<?php

namespace App\Http\Controllers\BitrixInstall;

use App\Http\Controllers\APIController;
use App\Http\Controllers\BitrixController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\PortalController;
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

                    ListController::setList($hook, $list, $currentPortalList);
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

    public static function setList($hook, $currentGoogleList, $currentPortalList)
    {


        // сначала обновляем или создаем на битриксе чтобы получить id
        // затем обновляем в портал или создаем и записываем туда id
        $method = '/lists.add';
        $url = $hook . $method;
        $listBtxCode = $currentGoogleList['group'] . ' ' . $currentGoogleList['code'];
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
        $createListResponse = Http::post($url, $btxListSetData);
        $resultList = BitrixController::getBitrixResponse($createListResponse, 'Create List - createListResponse');

        Log::channel('telegram')->info("Create BTX List", [
            'resultList' => $resultList,


        ]);




    }
}
