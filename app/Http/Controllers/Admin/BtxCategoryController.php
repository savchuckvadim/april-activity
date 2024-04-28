<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\APIController;
use App\Http\Controllers\Controller;
use App\Models\BtxCategory;
use Illuminate\Http\Request;

class BtxCategoryController extends Controller
{
    public static function getInitial($parentId = null, $parentType)
    {

        $initialData = BtxCategory::getForm($parentId, $parentType);
        $data = [
            'initial' => $initialData
        ];
        return APIController::getSuccess($data);
    }
}
