<?php

namespace App\Http\Controllers;

use App\Models\Counter;
use Illuminate\Http\Request;

class CounterController extends Controller
{
    public static function getInitial($templateId)
    {

        $initialData = Counter::getForm($templateId);
        $data = [
            'initial' => $initialData
        ];
        return APIController::getSuccess($data);
    }
}
