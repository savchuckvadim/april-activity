<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HookController extends Controller
{
    public static function getCalling($domain, $filters)
    {
        try {
            $baseUrl = 'https://april-hook.ru/api';
            $response = Http::post($baseUrl . '/calling', [
                'domain' => 'april-garant.bitrix24.ru',
                'filters' => $filters
            ]);
            $result = $response['data']['result'];
            Log::info('calling proxy ', ['callings ' => $result]);

            return  response([
                'result' => [
                    'calling' => $result,
                    'response' => $response,
                    'domain' => $domain,
                    'filters' => $filters,
                   
                
                ],
                'message' => 'success'
            ]);
        } catch (\Throwable $th) {
            Log::error('Exception caught', [
                'message'   => $th->getMessage(),
                'file'      => $th->getFile(),
                'line'      => $th->getLine(),
                'trace'     => $th->getTraceAsString(),
            ]);
            return response([
                'result' => 'error',
                'message' => $th->getMessage()
            ]);
        }
    }
}
