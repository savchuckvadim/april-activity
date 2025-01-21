<?php


use App\Http\Controllers\Front\Konstructor\ContractController;
use App\Http\Controllers\Front\Konstructor\InfoblockFrontController;
use App\Http\Controllers\Front\Konstructor\SupplyController;
use Illuminate\Support\Facades\Route;

Route::prefix('konstructor/front')->group(function () {
   
    Route::get('/iblocks', function () {
        $controller = new InfoblockFrontController();
        return $controller->getBlocks();
    });

    Route::get('/igroups', function () {
        $controller = new InfoblockFrontController();
        return $controller->getIGroups();
    });

});


