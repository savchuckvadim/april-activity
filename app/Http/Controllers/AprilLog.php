<?php

namespace App\Http\Controllers;

use GuzzleHttp\Promise\Promise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ALog extends Controller
{


    public static function push($message = '', $data)
    {

        Log::channel('telegram')->info('APRIL_ONLINE', [
            'PUSH LOG' => [
                'message' => $message,
                'data' => $data,


            ]
        ]);
        Log::info('APRIL_ONLINE', [
            'PUSH LOG' => [
                'message' => $message,
                'data' => $data,

            ]
        ]);
    }
}
