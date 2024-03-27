<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

class BitrixTelephony extends Controller
{
    public function statisticGet(Request $request)
    {

        $domain = 'april-garant.bitrix24.ru';
        $method = '/voximplant.statistic.get';
        // $hook = env('TEST_HOOK');
        // $requestData = $request->json();
        // $hook = BitrixController::getHook($domain);
        // $data = [
        //     'filter' => [
        //         'CALL_ID' => $request['data']['CALL_ID']
        //     ]
        // ];
        // Log::info('ALL_CALL_DATA: ', ['request->data' => $request['data']]);
        // Log::info('CALL_ID: ', ['CALL_ID' => $request['data']['CALL_ID']]);
        
        // Log::info('PHONE_NUMBER: ', ['PHONE_NUMBER' => $request['data']['PHONE_NUMBER']]);
    
        $content = $request->getContent();

        // Пытаемся разобрать XML
        try {
            $xml = new SimpleXMLElement($content);

            // Теперь $xml - это объект SimpleXMLElement, и вы можете обращаться к его элементам
            // Например, чтобы получить eventID:
            $eventID = (string) $xml->eventID;

            // Логируем полученный eventID
            Log::info('Received eventID: ', ['eventID' => $eventID]);

            // Вы можете добавить дополнительную логику обработки данных здесь
            // ...
            // Log::info('ALL_CALL_DATA: ', ['request' => $request]);
        } catch (Exception $e) {
            Log::error('Ошибка разбора XML: ' . $e->getMessage());
            // Обработка ошибки разбора XML
        }
    }
}
