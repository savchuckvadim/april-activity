<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class APIController extends Controller
{
    public static function getResponse($resultCode, $message, $data){

        return response([
            'resultCode' => $resultCode,
            'message' => $message,
            'data' => $data
        ]);
    }
    public static function getSuccess($data){

        return response([
            'resultCode' => 0,
            'message' => 'success',
            'data' => $data
        ]);
    }
    public static function getError($message, $data){

        return response([
            'resultCode' => 1,
            'message' => $message,
            'data' => $data
        ]);
    }
}
