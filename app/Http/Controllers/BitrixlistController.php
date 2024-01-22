<?php

namespace App\Http\Controllers;

use App\Models\Bitrixlist;
use Illuminate\Http\Request;

class BitrixlistController extends Controller
{
    public static function getInitial()
    {

        $initialData = Bitrixlist::getForm();
        $data = [
            'initial' => $initialData
        ];
        return APIController::getSuccess($data);
    }
}
