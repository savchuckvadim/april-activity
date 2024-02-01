<?php

namespace App\Http\Controllers;

use CRest;
use DateTime;
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
            $userFieldId = $request['filters']['userFieldId'];
            $userIds = $request['filters']['userIds'];
            $departament = $request['filters']['departament'];


            $actionFieldId = $request['filters']['actionFieldId'];
            $currentActionsData = $request['filters']['currentActions'];
            $dateFieldId = $request['filters']['dateFieldId'];
            $dateFrom = $request['filters']['dateFrom'];
            $dateTo = $request['filters']['dateTo'];
            $currentActions = [];
            $lists = [];

            if ($currentActionsData) {
                foreach ($currentActionsData as $id => $title) {
                    array_push($currentActions, $id);
                }
            }

            $controller = new BitrixController;
            $listsResponses = [];
            foreach ($departament as $user) {
                $userName =  $user['LAST_NAME'] . ' ' . $user['NAME'];
                $listsResponse = $controller->getReportLists(
                    $domain,
                    $userFieldId,
                    [$user['ID']],
                    $actionFieldId,
                    $currentActionsData,
                    $dateFieldId,
                    $dateFrom,
                    $dateTo
                );
                $userKPI = [
                    'user' => $user,
                    'userName' => $userName,
                    'kpi' => $listsResponse
                ];
                array_push($listsResponses, $userKPI);
            }


            // if ($listsResponse) {
            //     if (isset($listsResponse['data'])) {
            //         $lists = $listsResponse['data'];
            //     } else {
            //         if (isset($listsResponse['message']))
            //             return APIController::getError($listsResponse['message'], ['data' => $request->all()]);
            //     }
            // }

            if ($userIds && count($userIds) > 0) {

                foreach ($userIds as $userId) {
                }
            }
            return APIController::getSuccess(
                ['report' => [
                    'lists' => $listsResponse,
                    'listsResponses' => $listsResponses,
                    'userFieldId' => $userFieldId,
                    'userIds' => $userIds,
                    'actionFieldId' => $actionFieldId,
                    'currentActions' => $currentActions,
                    'dateFieldId' => $dateFieldId,
                    'dateFrom' => $dateFrom,
                    'dateFrom' => $dateFrom,
                    'dateTo' => $dateTo,

                ]]
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
            $actionUrl = '/voximplant.statistic.get.json';



            $hook = $controller->getHookUrl($domain);



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
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                [
                    'request' => $request
                ]
            );
        }
    }

    //protected report inner methods
    protected function getReportCallings($userId)
    {
    }
    protected function getReportLists(
        $domain,
        $userFieldId,
        $userIds,
        $actionFieldId,
        $currentActions,
        $dateFieldId,
        $dateFrom,
        $dateTo
    ) {
        // $domain
        // $action  - id поля в котором содержатся items действий
        // currentActions = массив айдишников действий которые нужно получить
        // date from
        // date to

        $method = '/lists.element.get.json';

        $listId = 86;

        $hook = $this->getHookUrl($domain);

        $url = $hook . $method;

        $result = [];

        foreach ($currentActions as $actionId => $actionTitle) {
            $data =   [
                'IBLOCK_TYPE_ID' => 'lists',
                // IBLOCK_CODE/IBLOCK_ID
                'IBLOCK_ID' => $listId,
                'FILTER' => [
                    $userFieldId => $userIds,
                    $actionFieldId => $actionId,
                    // '>' . $dateFieldId => $dateFrom,
                    // '<' . $dateFieldId => $dateTo,
                ]
            ];

            $response = Http::get($url, $data);
            if ($response) {
                if (isset($response['result'])) {

                    $otherData = [];
                    if (isset($response['next'])) {
                        $otherData['next'] = $response['next'];
                    }


                    $res = [
                        'action' => $actionTitle,
                        'count' =>  0
                    ];
                    if (isset($response['total'])) {
                        $res['count'] = $response['total'];
                    }
                    array_push($result, $res);
                }
            }
        }


        return $result;



        // $next = 0;
        // $allResults = [];
        // do {
        //     $response = Http::get($url, array_merge($data, ['next' => $next])); // Добавляем параметр start к запросу
        //     $responseBody = $response->json();

        //     if (isset($responseBody['result'])) {
        //         $allResults = array_merge($allResults, $responseBody['result']); // Собираем результаты
        //     }

        //     $next = $responseBody['next'] ?? null; // Обновляем start для следующего запроса

        // } while (!is_null($next));

        // return ['data' => $allResults];

    }





    public static function getBitrixCallingStatistics(Request $request)
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








        try {
            $domain = $request['domain'];
            $filters = $request['filters'];
            $callStartDateFrom = $filters['callStartDateFrom'];
            $callStartDateTo = $filters['callStartDateTo'];
            $controller = new BitrixController;
            $hook = $controller->getHookUrl($domain);

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
                    'request' => $request->all(),
                    'result' => $callingsTotalCount
                ]
            );
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                [
                    'request' => $request
                ]
            );
        }
    }
    public static function getBeelineStatistics(Request $request)
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

            $beelineData = json_decode($beelineResponse->body(), true);
            return APIController::getSuccess(

                [

                    'result' => $beelineData,

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
            $userId = $request->userId;
            $date = $request->date;
            $method = '/tasks.task.list.json';
            $controller = new BitrixController;
            $hook = $controller->getHookUrl($domain);



            // Ваша исходная строка с датой
            $dateString = $date;

            // Создаем объект DateTime из вашей строки
            $date = new DateTime($dateString);

            // Устанавливаем начало суток (00:00)
            $date->setTime(0, 0);

            // Выводим дату с началом суток
            $start = $date->format('Y-m-d H:i:s'); // Выведет "2023-12-29 00:00:00"

            // Устанавливаем конец суток (23:59)
            $date->setTime(23, 59);

            // Выводим дату с концом суток
            $finish = $date->format('Y-m-d H:i:s'); // Выведет "2023-12-29 23:59:00"



            if ($hook) {
                $url = $hook . $method;
                $data = [
                    'filter' => [
                        '>DEADLINE' => $start,
                        '<DEADLINE' => $finish,
                        'RESPONSIBLE_ID' => $userId
                    ]

                    // 'RESPONSIBLE_LAST_NAME' => $userId,
                    // 'GROUP_ID' => $date,
                ];

                $response = Http::get($url, $data);


                if (isset($response['result'])) {
                    if (isset($response['result']['tasks'])) {

                        $resultTasks = $response['result']['tasks'];
                    }
                } else if (isset($response['error_description'])) {
                    return APIController::getError(
                        $response['error_description'],
                        [
                            'response' => $response,
                            'date' => $date,
                            'data' => $data

                        ]

                    );
                }

                return APIController::getSuccess(

                    [
                        'tasks' => $resultTasks,
                        '$response' => $response,
                        'date' => $date,
                        'data' => $data


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
