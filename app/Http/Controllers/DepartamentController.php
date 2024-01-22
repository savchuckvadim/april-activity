<?php

namespace App\Http\Controllers;

use App\Models\Departament;
use Illuminate\Http\Request;

class DepartamentController extends Controller
{
    public static function getInitial()
    {

        $initialData = Departament::getForm();
        $data = [
            'initial' => $initialData
        ];
        return APIController::getSuccess($data);
    }
}
