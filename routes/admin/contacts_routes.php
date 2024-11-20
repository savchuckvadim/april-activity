<?php


use App\Http\Controllers\BtxContactController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
  // ........................................................................................... 
    // .........................................................................BTX COMPANIES

    //.................................... initial COMPANIES
    // initial from parent
    Route::get('initial/portal/{portalId}/contact', function ($portalId) {

        return BtxContactController::getInitial($portalId);
    });
    // single initial
    Route::get('initial/contact', function () {
        return BtxContactController::getInitial();
    });

    // .............................................GET  COMPANIES
    // all from parent  portal
    Route::get('portal/{portalId}/contacts', function ($portalId) {

        return BtxContactController::getByPortal($portalId);
    });
    // ...............  get company
    Route::get('contact/{contactId}', function ($contactId) {
        return BtxContactController::get($contactId);
    });


    // //...............................................SET COMPANY

    Route::post('portal/{portalId}/contact', function (Request $request) {

        return BtxContactController::store($request);
    });

    Route::post('contact/{contactId}', function (Request $request) {
        return BtxContactController::store($request);
    });

    // ............................................DELETE
    Route::delete('contact/{contactId}', function ($contactId) {
        return BtxContactController::delete($contactId);
    });


