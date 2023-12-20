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
}
