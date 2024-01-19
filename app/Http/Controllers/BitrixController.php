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

                                $webhookRestKey = $portal['data']['C_REST_WEB_HOOK_URL'];
                                $hook = 'https://' . $domain  . '/' . $webhookRestKey;
                                $actionUrl = '/voximplant.statistic.get.json';
                                $url = $hook . $actionUrl;
                                $next = 0; // Начальное значение параметра "next"

                                do {
                                    // Отправляем запрос на другой сервер
                                    $response = Http::get($url, [
                                        "FILTER" => [
                                            ">CALL_START_DATE" => $callStartDateFrom,
                                            "<CALL_START_DATE" =>  $callStartDateTo
                                        ],
                                        "start" => $next // Передаем значение "next" в запросе
                                    ]);
                                    Log::info('response', ['response' => $response]);

                                    if (isset($response['result']) && !empty($response['result'])) {
                                        // Добавляем полученные звонки к общему списку
                                        $resultCallings = array_merge($resultCallings, $response['result']);
                                        if (isset($response['next'])) {
                                            // Получаем значение "next" из ответа
                                            $next = $response['next'];
                                        } else {
                                            // Если ключ "next" отсутствует, выходим из цикла
                                            break;
                                        }
                                    }
                                    // Ждем некоторое время перед следующим запросом
                                    sleep(1); // Например, ждем 5 секунд

                                } while ($next > 0); // Продолжаем цикл, пока значение "next" больше нуля

                                return APIController::getSuccess(

                                    [
                                        'result' => $resultCallings, 'response' => $response,
                                        'callStartDateFrom' => $callStartDateFrom, 'callStartDateTo' => $callStartDateTo
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
