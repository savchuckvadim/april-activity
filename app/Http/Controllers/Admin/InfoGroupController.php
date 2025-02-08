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

        if ($resultGroups) {
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

    public static function store(Request $request)
    {

        try {
            //code...

            $validatedData = $request->validate([
                'id' => 'sometimes|integer|exists:info_groups,id',
                'name' => 'required|string',
                'title' => 'required|string',
                'code' => 'required|string',
                'type' => 'required|string',
                'description' => 'nullable|string',
                'number' => 'required|integer',
                'descriptionForSale' => 'nullable|string',
                'shortDescription' => 'nullable|string',
                'productType' => 'required|string',


            ]);
            $infogroup =  null;
            if (!empty($validatedData['id'])) {
                $infogroup = InfoGroup::find($validatedData['id']);
            }

            if (empty($infogroup)) {
                $infogroup = new InfoGroup($validatedData);
            }

            if (!empty($infogroup)) {
                $infogroup->save();
            }


            return APIController::getSuccess(
                $infogroup

            );
        } catch (\Throwable $th) {
            //throw $th;
            return APIController::getError(
                'InfoGroup was not updated',
                [$th->getMessage()]

            );
        }
    }

    public static function get($infoGroupId)
    {
        try {

            $infogroup = InfoGroup::find($infoGroupId);


            if (!$infogroup) {
                return response([
                    'resultCode' => 1,
                    'infoGroupId' => $infoGroupId,
                    'message' => 'infoGroup not found'
                ]);
            }

            return APIController::getResponse(
                0,
                'success',
                ['infogroup' => $infogroup]
            );
        } catch (\Throwable $th) {
            return APIController::getResponse(
                1,
                $th->getMessage(),
                null
            );
        }
    }
}
