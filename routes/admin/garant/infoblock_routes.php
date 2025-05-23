<?php

use App\Http\Controllers\Admin\ContractController;
use App\Http\Controllers\Admin\InfoblockController;
use App\Http\Controllers\Admin\InfoGroupController;
use App\Models\Garant\Infoblock;
use App\Models\Garant\InfoGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

    ///INFOBLOCKS

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


    //INFOBLOCK RECURSIVE RELATIONS
    Route::get('infoblock/{infoblockId}/relation', [InfoblockController::class, 'initRelations']);

    Route::post('infoblock/{infoblockId}/relation', [InfoblockController::class, 'storeRelations']);



    //INFO GROUP
    
    Route::post('infogroups', function (Request $request) {
        $infogroups  = $request->input('infogroups');

        return InfoGroupController::setInfoGroups($infogroups);
    });

    Route::get('initial/infogroup', [InfoGroupController::class, 'getInitial']);
    Route::post('infogroup', [InfoGroupController::class, 'store']);
    Route::post('infogroup/{infogroupId}', [InfoGroupController::class, 'store']);

    Route::get('infogroups', function () {
        // $infogroups  = InfoGroup::all();
        $infogroups = InfoGroup::with('infoblocks')->get();
        return response([
            'resultCode' => 0,
            'infogroups' =>  $infogroups
        ]);
    });
    Route::get('infogroup/{infogroupId}', function ($infogroupId) {
        return InfoGroupController::get($infogroupId);
    });
    Route::delete('infogroup/{infogroupId}', [InfoGroupController::class, 'delete']);

    Route::get('infogroup/{infogroupId}/relation', [InfoGroupController::class, 'initRelations']);

    Route::post('infogroup/{infogroupId}/relation', [InfoGroupController::class, 'storeRelations']);



