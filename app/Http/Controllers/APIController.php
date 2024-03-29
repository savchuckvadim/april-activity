<?php

namespace App\Http\Controllers;

use GuzzleHttp\Promise\Promise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class APIController extends Controller
{
    public static function getResponse($resultCode, $message, $data)
    {

        return response([
            'resultCode' => $resultCode,
            'message' => $message,
            'data' => $data
        ]);
    }
    public static function getSuccess($data)
    {

        return response([
            'resultCode' => 0,
            'message' => 'success',
            'data' => $data
        ]);
    }
    public static function getError($message, $data)
    {

        Log::channel('telegram')->error('APRIL_ONLINE', [
            'apiController' => [
                'message' => $message,
                $data

            ]
        ]);
        return response([
            'resultCode' => 1,
            'message' => $message,
            'data' => $data
        ]);
    }
}
