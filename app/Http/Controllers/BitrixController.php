<?php

namespace App\Http\Controllers;

use CRest;
use Illuminate\Http\Request;

class BitrixController extends Controller
{
    public static function hooktest()
    {
        return response(['hellou' => 'world']);
    }


    public static function connect($domain, $hoook)
    {
        define('C_REST_WEB_HOOK_URL', 'https://' . $domain . '/rest/1/' . $hoook); //url on creat Webhook
        return  CRest::checkServer();
    }
}
