<?php

use App\Http\Controllers\Admin\ContractController;
use App\Http\Controllers\Admin\InfoblockController;
use App\Http\Controllers\Admin\InfoGroupController;
use App\Models\Garant\Infoblock;
use App\Models\Garant\InfoGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

    ///INFOBLOCKS

    Route::post('infogroups', function (Request $request) {
        $infogroups  = $request->input('infogroups');

        return InfoGroupController::setInfoGroups($infogroups);
    });

    Route::post('infoblocks', function (Request $request) {
        $infoblocks  = $request->input('infoblocks');
        return InfoblockController::setInfoBlocks($infoblocks);
    });

    Route::post('infoblock/{infoblockId}', function ($infoblockId, Request $request) {
        return InfoblockController::updateInfoblock($infoblockId, $request);
    });
    Route::post('infoblock', function (Request $request) {
        return InfoblockController::setInfoblock($request);
    });



    Route::get('infoblocks', function () {
        $infoblocks  = Infoblock::all();
        return response([
            'resultCode' => 0,
            'infoblocks' =>  $infoblocks
        ]);
    });

    Route::get('infoblock/{infoblockId}', function ($infoblockId) {
        return InfoblockController::getInfoblock($infoblockId);
    });
    //INFO GROUP
    Route::get('initial/infogroup', [InfoGroupController::class, 'getInitial']);
    Route::post('infogroup', [InfoGroupController::class, 'store']);
    Route::post('infogroup/{infogroupId}', [InfoGroupController::class, 'store']);

    Route::get('infogroups', function () {
        $infogroups  = InfoGroup::all();
        return response([
            'resultCode' => 0,
            'infogroups' =>  $infogroups
        ]);
    });
    Route::get('infogroup/{infogroupId}', function ($infogroupId) {
        return InfoGroupController::get($infogroupId);
    });




