<?php

namespace App\Http\Controllers;

use App\Models\Smart;
use Illuminate\Http\Request;

class SmartController extends Controller
{
    public static function getInitial()
    {

        $initialData = Smart::getForm();
        $data = [
            'initial' => $initialData
        ];
        return APIController::getSuccess($data);
    }
}
