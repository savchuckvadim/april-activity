<?php

namespace App\Http\Controllers;

use App\Models\InfoGroup;
use Illuminate\Http\Request;

class InfoGroupController extends Controller
{
    public static function setInfoGroups($infogroups)
    {


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

            $newInfogroup = new InfoGroup();
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
        
        return response([
            'resultCode' => 0,
            'groups' => $resultGroups
        ]);
    }
}
