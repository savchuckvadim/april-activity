<?php

namespace App\Http\Controllers;

use App\Models\Counter;
use Illuminate\Http\Request;

class CounterController extends Controller
{
    public static function getInitial()
    {

        $initialData = Counter::getForm();
        $data = [
            'initial' => $initialData
        ];
        return APIController::getSuccess($data);
    }
}
