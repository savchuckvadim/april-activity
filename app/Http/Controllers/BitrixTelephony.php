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
            $ns = $xml->getNamespaces(true); // Получаем все пространства имен
              
            Log::info('xml', ['xml' => $xml]);
            Log::info('ns: ', ['ns' => $ns]);
            // Проверяем, существует ли пространство имен xsi
            if(isset($ns['xsi'])) {
                $xsiChildren = $xml->children($ns['xsi']);
                
                // Теперь $xsiChildren содержит элементы в пространстве имен xsi
                $eventID = (string) $xsiChildren->eventID;
                $userId = (string) $xsiChildren->userId;
                $extTrackingId = (string) $xsiChildren->extTrackingId;
                $targetId = (string) $xsiChildren->targetId;
                
                Log::info('Received eventID: ', ['eventID' => $eventID]);
                Log::info('userId: ', ['userId' => $userId]);
                Log::info('extTrackingId: ', ['extTrackingId' => $extTrackingId]);
                Log::info('targetId: ', ['targetId' => $targetId]);
            } else {
                Log::error('Пространство имен xsi не найдено в XML');
            }
        } catch (Exception $e) {
            Log::error('Ошибка разбора XML: ' . $e->getMessage());
            // Обработка ошибки разбора XML
        }
    }
}
