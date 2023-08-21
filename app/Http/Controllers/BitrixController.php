<?php

namespace App\Http\Controllers;

use App\Models\CRest;
use Illuminate\Http\Request;

class BitrixController extends Controller
{
    public static function hooktest()
    {
        return response(['hellou' => 'world']);
    }


    public static function connect()
    {
        // define('C_REST_WEB_HOOK_URL', 'https://' . $domain . '/rest/1/' . $hoook); //url on creat Webhook
        define('C_REST_CLIENT_ID', 'local.64e384619d5aa1.57865711'); //url on creat Webhook
        define('C_REST_CLIENT_SECRET', '62sEXPgn6xOhGHKBunzrcEmatBw9J3irdW9kXIoSfruHTnpLPx'); //url on creat Webhook
        $check = CRest::checkServer();
        $profile = CRest::call('profile');
        return $profile ;
    }
}
