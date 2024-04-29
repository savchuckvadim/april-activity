<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\APIController;
use App\Http\Controllers\Controller;
use App\Models\BtxStage;
use Illuminate\Http\Request;

class BtxStageController extends Controller
{
    public static function getInitial($categoryId = null)
    {

        $initialData = BtxStage::getForm($categoryId);
        $data = [
            'initial' => $initialData
        ];
        return APIController::getSuccess($data);
    }
}
