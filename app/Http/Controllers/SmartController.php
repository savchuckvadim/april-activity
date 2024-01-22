<?php

namespace App\Http\Controllers;

use App\Models\Smart;
use Illuminate\Http\Request;

class SmartController extends Controller
{
    public static function getInitial($portalId)
    {

        $initialData = Smart::getForm($portalId);
        $data = [
            'initial' => $initialData
        ];
        return APIController::getSuccess($data);
    }
}
