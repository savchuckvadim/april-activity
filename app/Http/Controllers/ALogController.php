<?php

namespace App\Http\Controllers;


use Illuminate\Support\Facades\Log;

class ALogController extends Controller
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
