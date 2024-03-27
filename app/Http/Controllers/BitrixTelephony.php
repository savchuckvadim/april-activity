<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BitrixTelephony extends Controller
{
    public function statisticGet(Request $request){

        $domain = 'april-garant.bitrix24.ru';
        $method = '/crm.activity.configurable.add.json';
        // $hook = env('TEST_HOOK');
        $hook = BitrixController::getHook($domain);
        Log::info('Звонок: ', ['$request' => $request->body()] );
    }
}
