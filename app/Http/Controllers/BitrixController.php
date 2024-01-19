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
            '30' => 30,
            '60' => 60,
            '180' => 180
        ];
        $errors = [];
        $responses = [];
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
                                $userId = 174;
                                // do {
                                // Отправляем запрос на другой сервер
                                foreach ($callingsTotalCount as $key => $duration) {
                                    if ($duration) {
                                        $data =   [
                                            "FILTER" => [
                                                "PORTAL_USER_ID" => [$userId],
                                                ">CALL_DURATION" => $duration,
                                                ">CALL_START_DATE" => $callStartDateFrom,
                                                "<CALL_START_DATE" =>  $callStartDateTo
                                            ]
                                        ];
                                    } else {
                                        $data =  ["FILTER" => [
                                            "PORTAL_USER_ID" => [$userId],
                                            ">CALL_START_DATE" => $callStartDateFrom,
                                            "<CALL_START_DATE" =>  $callStartDateTo
                                        ]];
                                    }

                                    $response = Http::get($url, $data);

                                    array_push($responses, $response);

                                    if (isset($response['total'])) {
                                        // Добавляем полученные звонки к общему списку
                                        // $resultCallings = array_merge($resultCallings, $response['result']);
                                        // if (isset($response['next'])) {
                                        //     // Получаем значение "next" из ответа
                                        //     $next = $response['next'];
                                        // }
                                        $callingsTotalCount[$key] = $response['total'];
                                    } else {
                                        array_push($errors, $response);
                                        $callingsTotalCount[$key] = 0;
                                    }
                                    // Ждем некоторое время перед следующим запросом
                                    sleep(1); // Например, ждем 5 секунд
                                }
                                // } while ($next > 0); // Продолжаем цикл, пока значение "next" больше нуля

                                return APIController::getSuccess(

                                    [
                                        'errors' => $errors,
                                        'responses' => $responses,
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


    public static function getDepartamentUsers(Request $request)
    {



        // FILTER	Массив может содержать поля в любом сочетании:
        // NAME - имя
        // LAST_NAME - фамилия
        // WORK_POSITION - должность
        // UF_DEPARTMENT_NAME - название подразделения
        // USER_TYPE - тип пользователя. Может принимать следующие значения:
        // employee - сотрудник,
        // extranet - пользователь экстранета,
        // email - почтовый пользователь

        $method = '/user.search.json';

        try {
            $domain = $request['domain'];
            $departamentId = 476;
            $portalResponse = PortalController::innerGetPortal($domain);
            if ($portalResponse) {
                if (isset($portalResponse['resultCode'])) {
                    if ($portalResponse['resultCode'] == 0) {
                        if (isset($portalResponse['portal'])) {
                            if ($portalResponse['portal']) {

                                $portal = $portalResponse['portal'];

                                $webhookRestKey = $portal['C_REST_WEB_HOOK_URL'];
                                $hook = 'https://' . $domain  . '/' . $webhookRestKey;
                                $actionUrl =  $method;
                                $url = $hook . $actionUrl;



                                $data =   [
                                    "FILTER" => [
                                        "UF_DEPARTMENT_NAME" => 'ЦУП',

                                    ]
                                ];


                                $response = Http::get($url, $data);
                                return APIController::getSuccess(

                                    [

                                        'response' => $response,
                                        'departament' => $response['result']
                                    ]
                                );
                            }


                            return APIController::getError(
                                'portal not found',
                                null
                            );
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
