<?php

namespace App\Http\Controllers;

use App\Jobs\BitrixDealUpdate;
use App\Models\Portal;
use App\Services\BitrixDealUpdateService;
use Carbon\Carbon;
use CRest;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Vipblogger\LaravelBitrix24\Bitrix;

class BitrixController extends Controller
{

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


    protected function getCallingGroupId($domain)
    {
        $callingGroupId = 28;
        try {


            $portalResponse = PortalController::innerGetPortal($domain);
            if ($portalResponse) {
                if (isset($portalResponse['resultCode'])) {
                    if ($portalResponse['resultCode'] == 0) {
                        if (isset($portalResponse['portal'])) {
                            if ($portalResponse['portal']) {

                                $portal = $portalResponse['portal'];
                                $callingGroups = $portal['callingGroups'];
                                foreach ($callingGroups as $group) {
                                    if (isset($group['name']) && isset($group['bitrixId'])) {

                                        if ($group['name'] === 'calling') {
                                            $callingGroupId = $group['bitrixId'];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            return  $callingGroupId;
        } catch (\Throwable $th) {
            return  $callingGroupId;
        }
    }

    public static function getHook($domain)
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

    public function getPortalReportData($domain)
    {
        $portal = Portal::where('domain', $domain)->first();
        return [
            'bitrixlistId' =>  $portal->getSalesBitrixListId()->bitrixId,
            'callingGroupId' =>  $portal->getSalesCallingGroupId()->bitrixId,
            'departamentId' =>  $portal->getSalesDepartamentId()->bitrixId,
        ];
    }

    public static function hooktest()
    {
        return response(['hellou' => 'world']);
    }


    protected function sendBatchRequest($domain, $commands)
    {
        $hook = $this->getHookUrl($domain); // Предполагаем, что функция getHookUrl уже определена
        $url = $hook . '/batch';
        $maxCommandsPerBatch = 50; // Максимальное количество команд на один batch запрос
        $batchRequests = array_chunk($commands, $maxCommandsPerBatch, true);
        $result = [
            // 'errors' => []
        ];

        foreach ($batchRequests as $batchCommands) {
            $response = Http::post($url, [
                'halt' => 0,
                'cmd' => $batchCommands
            ]);
            $responseData = $response->json();

            if (isset($responseData['result']['result_total']) && count($responseData['result']['result_total']) > 0) {
                foreach ($responseData['result']['result_total'] as $key => $kpiCount) {

                    $result[$key] = $kpiCount;
                }
            } else {
                // array_push($result['errors'], $responseData);
                // return APIController::getError('batch result not found', $responseData);
            }
            sleep(0.1);
        };

        return $result;
    }
    protected function processBatchResults($department, $currentActionsData, $batchResponseData)
    {
        $usersKPI = [];

        foreach ($department as $user) {
            $userName = $user['LAST_NAME'] . ' ' . $user['NAME'];
            $userKPI = [
                'user' => $user,
                'userName' => $userName,
                'kpi' => [],
                'callings' => []
            ];
            foreach ($currentActionsData as $actId => $actionTitle) {
                $kpiKey = 'user_' . $user['ID'] . '_action_' . $actId;
                $count = 0;
                foreach ($batchResponseData as $cmdKey => $cmdResult) {
                    if ($cmdKey == $kpiKey) {
                        $count = $cmdResult;
                    }
                }

                array_push($userKPI['kpi'], [
                    'id' => $actId,
                    'action' =>  $actionTitle,
                    'count' =>  $count,
                    'items' => []
                ]);
            }

            array_push($usersKPI, $userKPI);
        }

        // Перебор всех результатов в ответе от batch запроса
        // foreach ($batchResponseData as $cmdKey => $cmdResult) {
        //     // Разбиваем ключ команды, чтобы получить ID пользователя и ID действия
        //     list($userPrefix, $userId, $actionPrefix, $actionId) = explode('_', $cmdKey);

        //     // Находим информацию о пользователе и название действия
        //     $user = $department[array_search($userId, array_column($department, 'ID'))];
        //     $userName = $user['LAST_NAME'] . ' ' . $user['NAME'];
        //     $actionTitle = $currentActionsData[$actionId];

        //     // Подсчет результатов
        //     $count = isset($cmdResult) ? $cmdResult : 0;

        //     // Формирование структуры данных для пользователя и его KPI
        //     if (!isset($usersKPI[$userId])) {
        //         $usersKPI[$userId] = [
        //             'user' => $user,
        //             'userName' => $userName,
        //             'kpi' => []
        //         ];
        //         foreach ($currentActionsData as $actId => $actionTitle) {

        //             array_push($usersKPI[$userId]['kpi'], [
        //                 'id' => $actId,
        //                 'action' =>  $actionTitle,
        //                 'count' =>  0
        //             ]);
        //         }
        //         foreach ($usersKPI[$userId]['kpi'] as $initialKpiItem) {
        //             if ($initialKpiItem['id'] === $actionId) {
        //                 $initialKpiItem['count'] = $count;
        //             }
        //         }
        //         // $usersKPI[$userId]['kpi'][] = [
        //         //     'action' => $actionTitle,
        //         //     'count' => $count
        //         // ];
        //     }
        // }
        // array_push($usersKPI, $batchResponseData['errors']);
        return $usersKPI; // Возвращаем переиндексированный массив пользователей и их KPI
    }

    protected function cleanReport($report)
    {
        $kpiToRemove = []; // KPI для удаления

        // Собираем информацию о KPI для удаления
        foreach ($report as $user) {
            foreach ($user['kpi'] as $kpi) {
                $action = $kpi['action'];
                if (!isset($kpiToRemove[$action])) {
                    $kpiToRemove[$action] = ['zeroCount' => 0, 'totalCount' => 0];
                }
                $kpiToRemove[$action]['totalCount']++;
                if ($kpi['count'] == 0) {
                    $kpiToRemove[$action]['zeroCount']++;
                }
            }
        }

        // Определение KPI для удаления
        foreach ($kpiToRemove as $action => $data) {
            if ($data['zeroCount'] != $data['totalCount']) {
                unset($kpiToRemove[$action]);
            }
        }

        // Удаление ненужных KPI
        foreach ($report as &$user) {
            $user['kpi'] = array_values(array_filter($user['kpi'], function ($kpi) use ($kpiToRemove) {
                return !isset($kpiToRemove[$kpi['action']]);
            }));
        }
        unset($user); // Разорвать ссылку

        return $report;
    }

    protected function addTotalAndMediumKPI($report)
    {
        $totalKPI = []; // Суммарная информация по KPI
        $mediumKPI = []; // Средняя информация по KPI

        // Собираем суммарную информацию
        foreach ($report as $user) {
            foreach ($user['kpi'] as $kpi) {
                $action = $kpi['action'];
                if (!isset($totalKPI[$action])) {
                    $totalKPI[$action] = ['count' => 0, 'users' => 0];
                }
                $totalKPI[$action]['count'] += $kpi['count'];
                $totalKPI[$action]['users']++;
            }
        }

        // Вычисляем средние значения
        foreach ($totalKPI as $action => $data) {
            $mediumKPI[$action] = ['count' => 0];
            if ($data['users'] > 0) {
                $mediumKPI[$action]['count'] = $data['count'] / $data['users'];
            }
        }

        // Возвращаем дополненный отчет
        return [
            'total' => $totalKPI,
            'medium' => $mediumKPI,
        ];
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
        $result = [];

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

            $dateFieldForHookFrom = ">DATE_CREATE";
            $dateFieldForHookTo = "<DATE_CREATE";
            // $currentActions = [];
            // $lists = [];

            // if ($currentActionsData) {
            //     foreach ($currentActionsData as $id => $title) {
            //         array_push($currentActions, $id);
            //     }
            // }

            $controller = new BitrixController;
            $getPortalReportData = $controller->getPortalReportData($domain);
            $listId = $getPortalReportData['bitrixlistId'];

            $listsResponses = [];

            // Подготовка команд для batch запроса

            $commands = [];
            foreach ($departament as $user) {
                $userId = $user['ID'];
                $userName = $user['LAST_NAME'] . ' ' . $user['NAME'];

                foreach ($currentActionsData as $actionId => $actionTitle) {
                    // Формируем ключ команды, используя ID пользователя и ID действия для уникальности
                    $cmdKey = "user_{$userId}_action_{$actionId}";

                    // Добавляем команду в массив команд
                    $commands[$cmdKey] =
                        "lists.element.get?IBLOCK_TYPE_ID=lists&IBLOCK_ID=" . $listId . "&filter[$userFieldId]=$userId&filter[$actionFieldId]=$actionId&filter[$dateFieldForHookFrom]=$dateFrom&filter[$dateFieldForHookTo]=$dateTo";
                }
            }

            //lists
            // Отправляем batch запрос
            $batchResults = $controller->sendBatchRequest($domain, $commands);
            $report = $controller->processBatchResults($departament, $currentActionsData, $batchResults);
            $report = $controller->addVoximplantInReport($domain, $dateFrom, $dateTo, $report);
            $report = $controller->cleanReport($report);
            $totalReport = $controller->addTotalAndMediumKPI($report);

            //voximplant
            return APIController::getSuccess(
                [
                    'report' =>  $report,
                    // 'total' =>  $totalReport['total'],
                    // 'medium' =>  $totalReport['medium'],
                    'getPortalReportData' =>  $getPortalReportData,
                    'listId' =>  $listId,
                    // 'commands' =>  $commands

                ]
            );
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                [
                    '$batchResults' => $batchResults
                ]
            );
        }
    }

    protected function addVoximplantInReport($domain, $dateFrom, $dateTo, $report)
    {
        $callingsTypes = [
            [
                'id' => 'all',
                'action' => 'Наборов номера',
                'count' => 0,
                'duration' => 0,
                'period' => []
            ],
            [
                'id' => 30,
                'action' => 'Звонки > 30 сек',
                'count' => 0,
                'duration' => 0,
                'period' => []
            ],
            [
                'id' => 60,
                'action' => 'Звонки > минуты',
                'count' => 0,
                'duration' => 0,
                'period' => []
            ],
            [
                'id' => 180,
                'action' => 'Звонки > 3 минут',
                'count' => 0,
                'duration' => 0,
                'period' => []
            ],
            [
                'id' => 300,
                'action' => 'Звонки > 5 минут',
                'count' => 0,
                'duration' => 0,
                'period' => []
            ],
            [
                'id' => 600,
                'action' => 'Звонки > 10 минут',
                'count' => 0,
                'duration' => 0,
                'period' => []
            ],

        ];
        $errors = [];
        $responses = [];

        $result = [];

        $hook = $this->getHookUrl($domain);

        $actionUrl = '/voximplant.statistic.get.json';
        $url = $hook . $actionUrl;
        $next = 0; // Начальное значение параметра "next"


        foreach ($report as $k => $userReport) {

            $user = $userReport['user'];
            $userId = $user['ID'];
            $userIds = [$userId];
            $resultUserReport = $userReport;
            $resultUserReport['callings'] = $callingsTypes;



            foreach ($resultUserReport['callings'] as $key => $type) {

                if ($type['id'] === 'all') {
                    $data =   [
                        "FILTER" => [
                            "PORTAL_USER_ID" => $userIds,
                            // ">CALL_DURATION" => $type->duration,
                            ">CALL_START_DATE" => $dateFrom,
                            "<CALL_START_DATE" =>  $dateTo
                        ]
                    ];
                } else {
                    $data =   [
                        "FILTER" => [
                            "PORTAL_USER_ID" => $userIds,
                            ">CALL_DURATION" => $type['id'],
                            ">CALL_START_DATE" => $dateFrom,
                            "<CALL_START_DATE" =>  $dateTo
                        ]
                    ];
                }

                $response = Http::get($url, $data);

                array_push($responses, $response);

                // if (isset($response['total'])) {
                // Добавляем полученные звонки к общему списку
                // $resultCallings = array_merge($resultCallings, $response['result']);
                // if (isset($response['next'])) {
                //     // Получаем значение "next" из ответа
                //     $next = $response['next'];
                // }

                // $type['count'] = $response['total'];
                if (isset($response['total'])) {
                    $resultUserReport['callings'][$key]['count'] = $response['total'];
                }

                // } else { 
                //     return APIController::getError(
                //         'response total not found',
                //         [
                //             'response' => $response
                //         ]
                //     );
                //     array_push($errors, $response);
                //     $type['count'] = 0;
                // }
                // Ждем некоторое время перед следующим запросом
                // sleep(1); // Например, ждем 5 секунд
            }
            array_push($result, $resultUserReport);
            // } while ($next > 0); // Продолжаем цикл, пока значение "next" больше нуля
        }
        return  $result;
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

        // $fromProp = '>' . $dateFieldId;
        // $torop = '>' . $dateFieldId;

        foreach ($currentActions as $actionId => $actionTitle) {
            $data =   [
                'IBLOCK_TYPE_ID' => 'lists',
                // IBLOCK_CODE/IBLOCK_ID
                'IBLOCK_ID' => $listId,
                'FILTER' => [
                    $userFieldId => $userIds,
                    $actionFieldId => $actionId,
                    '>DATE_CREATE' => $dateFrom,
                    '<DATE_CREATE' => $dateTo,

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

            $controller = new BitrixController;
            $getPortalReportData = $controller->getPortalReportData($domain);
            $departamentId = $getPortalReportData['departamentId'];

            // $departamentId = 620;
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
        $domain = $request['domain'];
        $controller = new BitrixController;
        $getPortalReportData = $controller->getPortalReportData($domain);
        $listId = $getPortalReportData['bitrixlistId'];

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


    //KONSTRUCTOR


    public function konstructBitrixDealUpdate(
        $domain,
        $dealId,
        $setDealData,
        $updateDealInfoblocksData,
        $updateDealContractData,
        $setProductRowsData,
        $updateProductRowsData
    ) {
        if (!$dealId) {
            $hook = $this->getHook($domain);
            $method = '/crm.deal.add.json';
            $url = $hook . $method;
    
    
            $response = Http::get($url, $setDealData);
            $dealId =  $this->getBitrixRespone($response);
        }

        dispatch(new BitrixDealUpdate(
            $domain,
            $dealId,

            $updateDealInfoblocksData,
            $updateDealContractData,
            $setProductRowsData,
            $updateProductRowsData

        ));
       
        return APIController::getSuccess(['dealId' => $dealId]);
    }


    protected function getBitrixRespone($bitrixResponse)
    {
        $response =  $bitrixResponse->json();
        if ($response) {
            if (isset($response['result'])) {

                Log::info('success btrx response', [
                    'BTRX_RESPONSE_SUCCESS' => [
                        'result' => $response['result'],

                    ]

                ]);
                return $response['result'];
            } else {
                if (isset($response['error_description'])) {
                    Log::info('error', [
                        'SET_DEAL' => [
                            'btrx error' => $response['error'],
                            'btrx response' => $response['error_description']
                        ]

                    ]);
                    return null;
                }
            }
        }
    }


    ///CALLING

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
            $tasksGroupId = $controller->getCallingGroupId($domain);


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
                        'RESPONSIBLE_ID' => $userId,
                        'GROUP_ID' => $tasksGroupId,
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
                            'data' => $data,
                            'RESPONSIBLE_ID' => $userId

                        ]

                    );
                }

                return APIController::getSuccess(

                    [
                        'tasks' => $resultTasks,
                        '$response' => $response,
                        'date' => $date,
                        'data' => $data,
                        'RESPONSIBLE_ID' => $userId


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
    public function createTask(
        $domain,
        $placementId,
        $createdId,
        $responsibleId,
        $deadline,
        $name,
        $crm,
        $type
    ) {


        //TODO
        //  $createdId, нету то надо ставить БОС
        // или если не холодный то просто менять поля в карточке компании чтоб отуда уже создавалось




        try {
            // $portal = $portal['data'];

            $hook = $this->getHookUrl($domain);

            $portalData = $this->getPortalReportData($domain);
            $tasksGroupId = $portalData['callingGroupId'];
            //company and contacts
            $description = '';
            $lead = null;
            if (strpos($crm, "CO") !== false) {
                $methodContacts = '/crm.contact.list.json';
                $methodCompany = '/crm.company.get.json';
                $url = $hook . $methodContacts;

                $contactsData =  [
                    'FILTER' => [
                        'COMPANY_ID' => $placementId,

                    ],
                    'select' => ["ID", "NAME", "LAST_NAME", "SECOND_NAME", "TYPE_ID", "SOURCE_ID", "PHONE", "EMAIL", "COMMENTS"],
                ];

                $contacts = Http::get($url,  $contactsData);
                $url = $hook . $methodCompany;

                $getCompanyData = [
                    'ID'  => $placementId,
                    'select' => ["TITLE"],
                ];
                $company = Http::get($url,  $getCompanyData);





                $contactsString = '';

                foreach ($contacts['result'] as  $contact) {
                    $contactPhones = '';
                    foreach ($contact["PHONE"] as $phone) {
                        $contactPhones = $contactPhones .  $phone["VALUE"] . "   ";
                    }
                    $contactsString = $contactsString . "<p>" . $contact["NAME"] . " " . $contact["SECOND_NAME"] . " " . $contact["SECOND_NAME"] . "  "  .  $contactPhones . "</p>";
                }

                $companyTitleString = $company['result']['TITLE'];
                $description =  '<p>' . $companyTitleString . '</p>' . '<p> Контакты компании: </p>' . $contactsString;
            } else if (strpos($crm, "L") !== false) {

                $methodLead = '/crm.lead.get.json';

                $url = $hook . $methodLead;

                $getLeadData = [
                    'ID'  => $placementId,
                    'select' => ["TITLE"],
                ];
                $lead = Http::get($url,  $getLeadData);
                // $description = $lead['phone'];
            }

            //task
            $methodTask = '/tasks.task.add.json';
            $url = $hook . $methodTask;

            $nowDate = now();
            // $novosibirskTime = Carbon::createFromFormat('d.m.Y H:i:s', $deadline, 'Asia/Novosibirsk');
            // $moscowTime = $novosibirskTime->setTimezone('Europe/Moscow');
            // $moscowTime = $moscowTime->format('Y-m-d H:i:s');
            // Log::info('novosibirskTime', ['novosibirskTime' => $novosibirskTime]);
            // Log::info('moscowTime', ['moscowTime' => $moscowTime]);

            $title = $type['title'] . '  ' . $name . '  ' . $deadline;

            $taskData =  [
                'fields' => [
                    'TITLE' => $title,
                    'RESPONSIBLE_ID' => $responsibleId,
                    'GROUP_ID' => $tasksGroupId,
                    'CHANGED_BY' => $createdId, //- постановщик;
                    'CREATED_BY' => $createdId, //- постановщик;
                    'CREATED_DATE' => $nowDate, // - дата создания;
                    'DEADLINE' => $deadline, //- крайний срок;
                    // 'UF_CRM_TASK' => ['T9c_' . $crm],
                    'UF_CRM_TASK' => $crm,
                    'ALLOW_CHANGE_DEADLINE' => 'N',
                    'DESCRIPTION' => $description
                ]
            ];


            $responseData = Http::get($url, $taskData);
            $task = null;
            if (isset($responseData['result'])) {
                $task = $responseData['result'];
            }

            return APIController::getSuccess(['createdTask' => $task, '$lead' => $lead]);
        } catch (\Throwable $th) {
            Log::error('ERROR: Exception caught', [
                'message'   => $th->getMessage(),
                'file'      => $th->getFile(),
                'line'      => $th->getLine(),
                'trace'     => $th->getTraceAsString(),
            ]);
            return APIController::getError($th->getMessage(), null);
        }
    }


    public function changeDealStage($domain, $dealId, $stage)
    {
        $method = '/crm.deal.update.json';
        // $bitrixController = new BitrixController();
        if ($stage === 'offer') {
            $stage = "C6:PREPARATION";
            if ($domain == 'alfacentr.bitrix24.ru') {
                $stage = "C8:PREPARATION";
            } else if ($domain == 'gsirk.bitrix24.ru') {
                $stage = "C3:PREPARATION";
            } else if ($domain == 'april-garant.bitrix24.ru') {
                $stage = "C6:PREPARATION";
            }
        } else   if ($stage === 'invoice') {
            $stage = "C6:PREPARATION";
            if ($domain == 'alfacentr.bitrix24.ru') {
                $stage = "C8:PREPAYMENT_INVOICE";
            } else if ($domain == 'gsirk.bitrix24.ru') {
                $stage = "C3:1";
            } else if ($domain == 'april-garant.bitrix24.ru') {
                $stage = "C6:13";
            }
        }


        //TODO IF INVOICE
        // C8:PREPAYMENT_INVOICE





        try {
            $hook = BitrixController::getHook($domain); // Предполагаем, что функция getHookUrl уже определена

            $url = $hook . $method;
            $fields = [
                "STAGE_ID" => $stage,
                // "PROBABILITY" => 70
            ];
            $data = [
                'id' => $dealId,
                'fields' => $fields
            ];
            $response = Http::get($url, $data);
            if ($response) {
                if (isset($response['result'])) {
                    return APIController::getSuccess($response['result']);
                } else {
                    if (isset($response['error_description'])) {
                        return APIController::getError($response['error_description'], ['data' => [$domain, $dealId]]);
                    }
                }
            }
        } catch (\Throwable $th) {
            return APIController::getError($th->getMessage(), ['data' => [$domain, $dealId]]);
        }
    }
}
