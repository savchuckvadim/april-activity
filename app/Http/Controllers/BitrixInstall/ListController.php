<?php

namespace App\Http\Controllers\BitrixInstall;

use App\Http\Controllers\APIController;
use App\Http\Controllers\BitrixController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\PortalController;
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
            if (is_array($googleData) && !empty($googleData['lists'])) {
                $lists = $googleData['lists'];

                foreach ($lists as $list) {
                    $currentPortalSmart = null;
                    $currentBtxSmart = null;
                    $currentBtxSmartId = null;
                    foreach ($list['fields'] as $field) {
                        if (!empty($field['list'])) {
                            foreach ($field['list'] as $fieldItem) {


                                Log::channel('telegram')->error("LIST", [
                                    'fieldItem' => $fieldItem,


                                ]);
                            }
                        }
                    }

                   
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

    public static function setList()
    {
    }
}
