<?php

namespace App\Http\Controllers\Front\Konstructor;

use App\Http\Controllers\APIController;
use App\Http\Controllers\Controller;
use App\Models\Infoblock;
use Illuminate\Http\Request;

class InfoblockFrontController extends Controller
{
    public function getBlocks()
    {
        $iblocks = Infoblock::all();
        
        return APIController::getSuccess($iblocks);
    }
}
