<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BitrixTelephony extends Controller
{
    public function statisticGet(Request $request)
    {

        $domain = 'april-garant.bitrix24.ru';
        $method = '/voximplant.statistic.get';
        // $hook = env('TEST_HOOK');
        // $requestData = $request->json();
        $hook = BitrixController::getHook($domain);
        // $data = [
        //     'filter' => [
        //         'CALL_ID' => $request['data']['CALL_ID']
        //     ]
        // ];
        Log::info('Звонок: ', ['$request' => $request['data']]);
    }
}
