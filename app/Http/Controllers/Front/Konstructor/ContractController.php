<?php

namespace App\Http\Controllers\Front\Konstructor;

use App\Http\Controllers\APIController;
use App\Http\Controllers\Controller;
use App\Models\Portal;
use App\Models\PortalContract;
use Illuminate\Http\Request;

class ContractController extends Controller
{
  

  
    /**
     * Display the specified resource.
     */
    public function get($portalContractId) //by id
    {
        try {
            $portalContract = PortalContract::find($portalContractId);

            if ($portalContract) {
                // $resultSmart = new SmartResource($rpa);
                return APIController::getSuccess(
                    ['portalcontract' => $portalContract]
                );
            } else {
                return APIController::getError(
                    'portalcontract was not found',
                    ['portalcontract' => $portalContract]
                );
            }
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                ['portalcontractId' => $portalContract]
            );
        }
    }

    public  function getAll()
    {

        // Создание нового Counter
        $portalcontracts = PortalContract::all();
        if ($portalcontracts) {

            return APIController::getSuccess(
                ['portalcontracts' => $portalcontracts]
            );
        }


        return APIController::getSuccess(
            ['portalcontracts' => []]
        );
    }



    // public function getByDomain($domain)
    // {
    //     $portalcontracts = [];
    //     // Создание нового Counter
    //     $portal = Portal::find($portalId);
    //     if ($portal) {
    //         $portalcontracts = $portal->portalcontracts;
    //         if ($portalcontracts) {

    //             return APIController::getSuccess(
    //                 ['portalcontracts' => $portalcontracts]
    //             );
    //         }
    //     }


    //     return APIController::getSuccess(
    //         ['portalcontracts' => $portalcontracts]
    //     );
    // }
    /**
     * Show the form for editing the specified resource.
     */

    public function destroy($portalcontractId)
    {
        $portalcontract = PortalContract::find($portalcontractId);

        if ($portalcontract) {
            // Получаем все связанные поля
            $portalcontract->delete();
            return APIController::getSuccess($portalcontract);
        } else {

            return APIController::getError(
                'portalcontract not found',
                null
            );
        }
    }
}
