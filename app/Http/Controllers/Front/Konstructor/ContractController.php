<?php

namespace App\Http\Controllers\Front\Konstructor;

use App\Http\Controllers\APIController;
use App\Http\Controllers\Controller;
use App\Models\BitrixfieldItem;
use App\Models\Portal;
use App\Models\PortalContract;
use Illuminate\Http\Request;

class ContractController extends Controller
{


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

    public function getByPortal(Request $request)
    {
        $domain =  $request->domain;
        $portalcontracts = [];
        $resultContracts = [];
        // Создание нового Counter
        $portal = Portal::where('domain', $domain)->first();
        if ($portal) {
            $portalcontracts = $portal->contracts;
            if (!empty($portalcontracts)) {

                foreach ($portalcontracts as $portalcontract) {
                    $resultContract = $portalcontract;
                    if (!empty($portalcontract['contract'])) {
                        $resultContract['code'] = $portalcontract['contract']['code'];
                        $resultContract['number'] = $portalcontract['contract']['number'];


                        if (empty($portalcontract['productName'])) {
                            $resultContract['productName'] = $portalcontract['contract']['productName'];
                        }
                        if (!empty($portalcontract['portal_measure'])) {

                            if (!empty($portalcontract['portal_measure']['bitrixId'])) {

                                $resultContract['bitrixMeasureId'] = (int)$portalcontract['portal_measure']['bitrixId'];
    
                            }
                        }


                    }

                    // $fieldItem = BitrixfieldItem::find($portalcontract['contract']['bitrixfield_item_id']);
                    // $field = $fieldItem->bitrixfield;
                    // $resultContracts['fieldItem'] = $fieldItem;
                    // $resultContracts['field'] = $field;

                    array_push($resultContracts, $resultContract);
                }




                return APIController::getSuccess(
                    ['portalcontracts' => $resultContracts]
                );
            }
        }


        return APIController::getSuccess(
            ['contracts' => $portalcontracts, 'portal' => $portal]
        );
    }
}
