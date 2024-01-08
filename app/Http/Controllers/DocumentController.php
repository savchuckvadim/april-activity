<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function getDocument($data)
    {
        return APIController::getSuccess([
            'data' => $data,
            'infoblocks' => $this->getInfoblocks()
        ]);
    }

    protected function getInfoblocks()
    {
        return 'infoblocks';
    }
}
