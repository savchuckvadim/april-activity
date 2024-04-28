<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\APIController;
use App\Http\Controllers\Controller;
use App\Models\Timezone;
use Illuminate\Http\Request;

class TimezoneController extends Controller
{
    public static function getInitial($portalId)
    {

        $initialData = Timezone::getForm($portalId);
        $data = [
            'initial' => $initialData
        ];
        return APIController::getSuccess($data);
    }
}
