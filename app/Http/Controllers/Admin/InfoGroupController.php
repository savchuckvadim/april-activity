<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\APIController;
use App\Http\Controllers\Controller;
use App\Models\Garant\InfoGroup;
use Illuminate\Http\Request;

class InfoGroupController extends Controller
{
    public static function getInitial()
    {

        $initialData = InfoGroup::getForm();
        $data = [
            'initial' => $initialData
        ];
        return APIController::getSuccess($data);
    }

    public static function setInfoGroups($infogroups)
    {
        $resultGroups = null;
        $resultCode = 1;
        $message = 'something wrong with infogroups online';
        $data = null;
        // $infoblocks = [
        //     ['code' => 'npa',
        //     'name' => 'НПА',
        //     'title' => 'Hohvfnbdy...',
        //     'description' => 'Hohvfnbdy...',
        //     'descriptionForSale' => 'Hohvfnbdy...',
        //     'shortDescription' => 'Hohvfnbdy...',],
        //     ...

        // ];


        foreach ($infogroups as $infogroup) {

            $newInfogroup = InfoGroup::where('code', $infogroup['code'])->first();
            if (!$newInfogroup) {
                $newInfogroup = new InfoGroup();
            }

            $newInfogroup['number'] = $infogroup['number'];
            $newInfogroup['code'] = $infogroup['code'];
            $newInfogroup['name'] = $infogroup['name'];
            $newInfogroup['title'] = $infogroup['title'];
            $newInfogroup['description'] = $infogroup['description'];
            $newInfogroup['descriptionForSale'] = $infogroup['descriptionForSale'];
            $newInfogroup['shortDescription'] = $infogroup['shortDescription'];
            $newInfogroup['type'] = $infogroup['type'];
            $newInfogroup['productType'] = $infogroup['productType'];

            $newInfogroup->save();
        }
        $resultGroups = InfoGroup::all();

        if($resultGroups){
            $resultCode = 0;
            $message = null;
            $data =  $resultGroups;
        }


        
        return response([
            'resultCode' => $resultCode,
            'data' => $data,
            'message' => $message
        ]);
    }
}
