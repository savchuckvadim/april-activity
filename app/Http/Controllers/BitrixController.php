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
        $userIds = [];
        $beelineResponse = null;
        try {
            $beelineResponse = Http::withHeaders([
                'X-MPBX-API-AUTH-TOKEN' => '0c506738-88a9-47ec-8c6c-c0f938027317',
                // 'Another-Header' => 'Another-Value',
            ])->get('https://cloudpbx.beeline.ru/apis/portal/v2/statistics?userId=703&dateFrom=2024-01-01T00%3A00%3A00Z&dateTo=2024-01-24T00%3A00%3A00Z&page=0&pageSize=100');

            Log::info('BEELINE', ['beelineResponse' => $beelineResponse]);
            return APIController::getSuccess(

                [

                    'beelineResponse' => $beelineResponse->body(),

                ]
            );
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                [
                    'beelin error' => $request
                ]
            );
        }





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
                                // $userId = 174;
                                // do {
                                // Отправляем запрос на другой сервер

                                // if (isset($filters) && isset($filters['userIds'])) {
                                $userIds = $filters['userIds'];
                                // }




                                foreach ($callingsTotalCount as $key => $duration) {
                                    if ($duration) {
                                        $data =   [
                                            "FILTER" => [
                                                "PORTAL_USER_ID" => $userIds,
                                                ">CALL_DURATION" => $duration,
                                                ">CALL_START_DATE" => $callStartDateFrom,
                                                "<CALL_START_DATE" =>  $callStartDateTo
                                            ]
                                        ];
                                    } else {
                                        $data =  ["FILTER" => [
                                            "PORTAL_USER_ID" => $userIds,
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
            $departamentId = 620;
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
                                        "UF_DEPARTMENT" => $departamentId,
                                        'ACTIVE' => true

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
    public static function getList(Request $request)
    {


        $method = '/lists.element.get.json';
        $fieldsMethod = 'lists.field.type.get';
        $listId = 86;

        try {
            $domain = $request['domain'];

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
                                    'IBLOCK_TYPE_ID' => 'lists',
                                    // IBLOCK_CODE/IBLOCK_ID
                                    'IBLOCK_ID' => $listId
                                ];


                                $response = Http::get($url, $data);
                                $fieldsResponse = Http::get($hook . $fieldsMethod, $data);
                                if (isset($response['result'])) {
                                    return APIController::getSuccess(

                                        [

                                            'response' => $response,
                                            'list' => $response['result'],
                                            'fieldsMethod' => $fieldsResponse['result']
                                        ]
                                    );
                                } else {
                                    Log::info('Response error ', [

                                        'response' => $response,

                                    ]);
                                    if (isset($response['error'])) {
                                        return APIController::getError(
                                            'request error',
                                            [

                                                'response' => $response,
                                                'error' => $response['error'],
                                                'description' => $response['error_description']
                                            ]
                                        );
                                    }
                                    return APIController::getError(
                                        'request error',

                                        [

                                            'response' => $response,
                                            // 'request' => $response
                                        ]
                                    );
                                }
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
    public static function getListFilter(Request $request)
    {


        $method = '/lists.field.get.json';

        $listId = 86;

        try {
            $domain = $request['domain'];

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
                                    'IBLOCK_TYPE_ID' => 'lists',
                                    // IBLOCK_CODE/IBLOCK_ID
                                    'IBLOCK_ID' => $listId
                                ];


                                $response = Http::get($url, $data);

                                if (isset($response['result'])) {
                                    return APIController::getSuccess(

                                        [

                                            'response' => $response,
                                            'filter' => $response['result'],

                                        ]
                                    );
                                } else {
                                    Log::info('Response error ', [

                                        'response' => $response,

                                    ]);
                                    if (isset($response['error'])) {
                                        return APIController::getError(
                                            'request error',
                                            [

                                                'response' => $response,
                                                'error' => $response['error'],
                                                'description' => $response['error_description']
                                            ]
                                        );
                                    }
                                    return APIController::getError(
                                        'request error',

                                        [

                                            'response' => $response,
                                            // 'request' => $response
                                        ]
                                    );
                                }
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


    public static function getCompany(Request $request)
    {




        try {

            $domain = $request->domain;
            $companyId = $request->companyId;
            $method = '/crm.company.get.json';
            $resultCompany = null;
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

                                $url = $hook . $method;
                                $data = [
                                    'id' => $companyId
                                ];

                                $response = Http::get($url, $data);
                                if (isset($response['result'])) {

                                    $resultCompany = $response['result'];
                                } else if (isset($response['error_description'])) {
                                    return APIController::getError(
                                        $response['error_description'],
                                        [
                                            'response' => $response

                                        ]

                                    );
                                }

                                return APIController::getSuccess(

                                    [
                                        'company' => $resultCompany,


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

    public static function getDeal(Request $request)
    {

        try {

            $domain = $request->domain;
            $dealId = $request->dealId;
            $method = '/crm.deal.get.json';
            $resultDeal = null;
            $portalResponse = PortalController::innerGetPortal($domain);
            if ($portalResponse) {
                if (isset($portalResponse['resultCode'])) {
                    if ($portalResponse['resultCode'] == 0) {
                        if (isset($portalResponse['portal'])) {
                            if ($portalResponse['portal']) {

                                $portal = $portalResponse['portal'];

                                $webhookRestKey = $portal['C_REST_WEB_HOOK_URL'];
                                $hook = 'https://' . $domain  . '/' . $webhookRestKey;

                                $url = $hook . $method;
                                $data = [
                                    'id' => $dealId
                                ];

                                $response = Http::get($url, $data);
                                Log::info('GET DEAL', ['response' => $response]);

                                if (isset($response['result'])) {

                                    $resultDeal = $response['result'];
                                } else if (isset($response['error_description'])) {
                                    return APIController::getError(
                                        $response['error_description'],
                                        [
                                            'response' => $response

                                        ]

                                    );
                                }

                                return APIController::getSuccess(

                                    [
                                        'deal' => $resultDeal,
                                        '$response' => $response


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


    public static function getCallingTasks(Request $request)
    {
        $resultTasks = [];
        try {
            $domain = $request->domain;
            $userId = $request->dealId;
            $date = $request->date;
            $method = '/tasks.task.list.json';
            $controller = new BitrixController;
            $hook = $controller->getHookUrl($domain);

            if ($hook) {
                $url = $hook . $method;
                $data = [
                    'DEADLINE' => $date,
                    // 'RESPONSIBLE_LAST_NAME' => $userId,
                    // 'GROUP_ID' => $date,
                ];

                $response = Http::get($url, $data);
                Log::info('GET DEAL', ['response' => $response]);

                if (isset($response['result'])) {
                    if (isset($response['result']['tasks'])) {

                        $resultTasks = $response['result']['tasks'];
                    }
                } else if (isset($response['error_description'])) {
                    return APIController::getError(
                        $response['error_description'],
                        [
                            'response' => $response

                        ]

                    );
                }

                return APIController::getSuccess(

                    [
                        'tasks' => $resultTasks,
                        '$response' => $response


                    ]
                );
            } else {
                return APIController::getError(
                    'hook not found',
                    [
                        'hook' => $hook
                    ]
                );
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    protected function getHookUrl($domain)
    {
        $hook = null;
        try {


            $portalResponse = PortalController::innerGetPortal($domain);
            if ($portalResponse) {
                if (isset($portalResponse['resultCode'])) {
                    if ($portalResponse['resultCode'] == 0) {
                        if (isset($portalResponse['portal'])) {
                            if ($portalResponse['portal']) {

                                $portal = $portalResponse['portal'];

                                $webhookRestKey = $portal['C_REST_WEB_HOOK_URL'];
                                $hook = 'https://' . $domain  . '/' . $webhookRestKey;
                            }
                        }
                    }
                }
            }
            return $hook;
        } catch (\Throwable $th) {
            return $hook;
        }
    }
}
