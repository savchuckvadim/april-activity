<?php

namespace App\Http\Controllers;

use CRest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Vipblogger\LaravelBitrix24\Bitrix;

class BitrixController extends Controller
{
    public static function hooktest()
    {
        return response(['hellou' => 'world']);
    }


    public static function getReport(Request $request)
    {
        $callingsTotalCount = [
            'all' => null,
            '30' => 0,
            '60' => 0,
            '180' => 0
        ];
        try {
            $domain = $request['domain'];
            $filters = $request['filters'];
            $callStartDateFrom = $filters['callStartDateFrom'];
            $callStartDateTo = $filters['callStartDateTo'];
            $portalResponse = PortalController::innerGetPortal($domain);
            if ($portalResponse) {
                if (isset($portalResponse['resultCode'])) {
                    if ($portalResponse['resultCode'] == 0) {
                        if (isset($portalResponse['portal'])) {
                            if ($portalResponse['portal']) {
                                $resultCallings = [];
                                $portal = $portalResponse['portal'];

                                $webhookRestKey = $portal['C_REST_WEB_HOOK_URL'];
                                $hook = 'https://' . $domain  . '/' . $webhookRestKey;
                                $actionUrl = '/voximplant.statistic.get.json';
                                $url = $hook . $actionUrl;
                                $next = 0; // Начальное значение параметра "next"
                                $userId = 107;
                                // do {
                                // Отправляем запрос на другой сервер
                                foreach ($callingsTotalCount as $key => $duration) {
                                    if ($duration) {
                                        $data =   [
                                            "FILTER" => [
                                                "USER_ID" => $userId,
                                                ">CALL_DURATION" => $duration,
                                                ">CALL_START_DATE" => $callStartDateFrom,
                                                "<CALL_START_DATE" =>  $callStartDateTo
                                            ]
                                        ];
                                    } else {
                                        ["FILTER" => [
                                            "USER_ID" => $userId,
                                            ">CALL_START_DATE" => $callStartDateFrom,
                                            "<CALL_START_DATE" =>  $callStartDateTo
                                        ]];
                                    }

                                    $response = Http::get($url, $data);


                                    if (isset($response['total'])) {
                                        // Добавляем полученные звонки к общему списку
                                        // $resultCallings = array_merge($resultCallings, $response['result']);
                                        // if (isset($response['next'])) {
                                        //     // Получаем значение "next" из ответа
                                        //     $next = $response['next'];
                                        // }
                                        $callingsTotalCount[$key] = $response['total'];
                                    }
                                    // Ждем некоторое время перед следующим запросом
                                    sleep(1); // Например, ждем 5 секунд
                                }
                                // } while ($next > 0); // Продолжаем цикл, пока значение "next" больше нуля

                                return APIController::getSuccess(

                                    [

                                        'result' => $callingsTotalCount
                                    ]
                                );
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                [
                    'request' => $request
                ]
            );
        }
    }



    public static function connect($bitrix)
    {
        // define('C_REST_WEB_HOOK_URL', 'https://' . $domain . '/rest/1/' . $hoook); //url on creat Webhook
        define('C_REST_CLIENT_ID', ''); //url on creat Webhook
        define('C_REST_CLIENT_SECRET', ''); //url on creat Webhook

        $profile = $bitrix->call('profile');
        return $profile;
    }
}
