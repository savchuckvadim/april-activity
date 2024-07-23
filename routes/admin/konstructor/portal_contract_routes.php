<?php

use App\Http\Controllers\Admin\PortalContractController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// Route::prefix('smart')->group(function () {
//     Route::get('initial/portal/{portalId}', [SmartController::class, 'getInitial']);
//     Route::get('initial', [SmartController::class, 'getInitial']);
//     Route::get('{smartId}', [SmartController::class, 'get']);
//     Route::post('{portalId}', [SmartController::class, 'store']);
//     Route::delete('{smartId}', [SmartController::class, 'delete']);
// });



// ......................................................................... SMARTS

//.................................... initial RPA
// initial from parent
Route::get('initial/portal/{portalId}/portalcontract', [PortalContractController::class, 'getInitial']);
// single initial


// .............................................GET  SMART
// all from parent  smart
Route::get('portal/{portalId}/portalcontracts', [PortalContractController::class, 'getByPortal']);

// // ...............  get smart
Route::get('portalcontract/{portalcontractId}', [PortalContractController::class, 'get']);


//...............................................SET RPA

Route::post('portal/{portalId}/portalcontract', [PortalContractController::class, 'store']);
// Route::post('measure/{measureId}', [PortalMeasure::class, 'store']);

// ............................................DELETE
Route::delete('portal/portalcontract/{portalcontractId}', [PortalContractController::class, 'destroy']);


//FIELDS
