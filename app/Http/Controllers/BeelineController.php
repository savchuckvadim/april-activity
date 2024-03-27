<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BeelineController extends Controller
{


    public function getSabcribe()
    {
        // $data = $request->all(); // Получаем данные из запроса
    


        $response = Http::withHeaders([
            'X-MPBX-API-AUTH-TOKEN' => env('BEELINE_KEY'),
        ])->put('https://cloudpbx.beeline.ru/apis/portal/subscription', [
            'pattern' => '600',
            'expires' => 3600,
            'subscriptionType' => 'BASIC_CALL',
            'url' => 'https://april-online.ru/api/innerhook/call' // Укажите реальный URL вашего обработчика
        ]);
        Log::channel('console')->info('Получено уведомление о звонке: ', ['response' => $response]);
        Log::info('Получено уведомление о звонке: ',  ['response' => $response]);
        return  $response;
    }
}
