<?php

namespace App\Http\Controllers;

use App\Models\Calling;
use Illuminate\Http\Request;

class CallingController extends Controller
{
    public static function getInitial()
    {

        $initialData = Calling::getForm();
        $data = [
            'initial' => $initialData
        ];
        return APIController::getSuccess($data);
    }
}
