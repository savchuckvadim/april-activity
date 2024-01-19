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
            $getFields = Http::get($baseUrl . '/calling', [
                'domain' => 'april-garant.bitrix24.ru'
            ]);
            Log::info('calling proxy ', ['fields ' => $getFields]);

            return  response([
                'result' => ['calling' => $getFields],
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
