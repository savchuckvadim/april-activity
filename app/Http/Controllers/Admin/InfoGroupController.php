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

            $request->merge([
                'withABS' => filter_var($request->input('withABS'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
                'withConsalting' => filter_var($request->input('withConsalting'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
                'withServices' => filter_var($request->input('withServices'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
                'withLt' => filter_var($request->input('withLt'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
                'isChanging' => filter_var($request->input('isChanging'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
                'withDefault' => filter_var($request->input('withDefault'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            ]);

            $validatedData = $request->validate([
                'id' => 'sometimes|integer|exists:info_groups,id',
                'name' => 'required|string',
                'title' => 'required|string',

                'code' => 'required|string',
                'type' => 'required|string',
                'description' => 'sometimes|nullable|string',
                'number' => 'required|integer|string',
                'descriptionForSale' => 'required|string',
                'shortDescription' => 'required|string',
                'productType' => 'required|string',


            ]);
            $currentComplect =  null;
            if (!empty($validatedData['id'])) {
                $currentComplect = InfoGroup::find($validatedData['id']);
            }

            if (empty($currentComplect)) {
                $currentComplect = new InfoGroup($validatedData);
            }

            if (!empty($currentComplect)) {
                $currentComplect->save();
            }


            return APIController::getSuccess(
                ['complect' => $currentComplect]

            );
        } catch (\Throwable $th) {
            //throw $th;
            return APIController::getError(
                'complect was not updated',
                [$th->getMessage()]

            );
        }
    }
}
