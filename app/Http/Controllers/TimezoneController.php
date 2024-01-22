<?php

namespace App\Http\Controllers;

use App\Models\Timezone;
use Illuminate\Http\Request;

class TimezoneController extends Controller
{
    public static function getInitial()
    {

        $initialData = Timezone::getForm();
        $data = [
            'initial' => $initialData
        ];
        return APIController::getSuccess($data);
    }
}
