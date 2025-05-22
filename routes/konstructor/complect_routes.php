<?php

use App\Http\Controllers\Admin\Garant\ComplectController;
use App\Models\Garant\Infoblock;
use Illuminate\Support\Facades\Route;


Route::prefix('garant')->group(function () {

    Route::get('complects', [ComplectController::class, 'getAll']);
    Route::get('complect/{complectId}', [ComplectController::class, 'get']);
    Route::get('complect/{complectId}/infoblocks', [ComplectController::class, 'infoblocks']);
    Route::get('complect-infoblock/{infoblockId}', [ComplectController::class, 'infoblock']);
    Route::get('infoblocks', function () {
        $infoblocks  = Infoblock::with(['group', 'parentPackages', 'inPackage', 'complects'])->get();
        return response([
            'resultCode' => 0,
            'infoblocks' =>  $infoblocks
        ]);
    });});
