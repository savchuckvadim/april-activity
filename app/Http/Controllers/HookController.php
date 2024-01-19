<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HookController extends Controller
{
    public static function getCalling()
    {
        try {
            $baseUrl = 'https://april-hook.ru/api';
            $response = Http::post($baseUrl . '/calling', [
                'domain' => 'april-garant.bitrix24.ru'
            ]);
            Log::info('calling proxy ', ['callings ' => $response['result']]);

            return  response([
                'result' => ['calling' => $response['result']],
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
