<?php

namespace App\Http\Controllers\Front\Konstructor;

use App\Http\Controllers\APIController;
use App\Http\Controllers\BitrixController;
use App\Http\Controllers\Controller;
use App\Http\Resources\PortalContractResource;
use App\Models\Bitrixfield;
use App\Models\BitrixfieldItem;
use App\Models\Portal;
use App\Models\PortalContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ContractController extends Controller
{

    public function frontInit(Request $request) //by id
    {
        $data = $request->all();
        $domain = $data['domain'];
        $companyId = $data['companyId'];
        try {
            $portal = Portal::where('domain', $domain)->first();

            $providers = $portal->providers;
            $hook = BitrixController::getHook($domain);
            $result = [
                'providers' => $providers,
                'client' => [
                    'rq' => [],
                    'bank' => [],
                    'address' => [],
                ],
                'provider' => [
                    'rq' => [],
                    'bank' => [],
                    'address' => [],
                ],
            ];
            $rqMethod = '/crm.requisite.list';
            $rqData = [
                'filter' => [
                    'ENTITY_TYPE_ID' => 4,
                    'ENTITY_ID' => $companyId,
                ]
            ];
            $url = $hook . $rqMethod;
            $responseData = Http::post($url,  $rqData);
            $result['client']['rq']  = BitrixController::getBitrixResponse($responseData, $rqMethod);


            //bank
            if (!empty($result['client']['rq'])) {
                $clientRq = $result['client']['rq'][0];
                if (!empty($clientRq) && isset($clientRq['ID'])) {


                    $result['client']['rq']  = $clientRq;
                    $rqId = $result['client']['rq']['ID'];
                    $bankMethod = '/crm.requisite.bankdetail.list';
                    $bankData = [
                        'filter' => [
                            // 'ENTITY_TYPE_ID' => 4,
                            'ENTITY_ID' => $rqId,
                        ]
                    ];
                    $url = $hook . $bankMethod;
                    $responseData = Http::post($url,  $bankData);
                    $result['client']['bank']  = BitrixController::getBitrixResponse($responseData, $bankMethod);


                    //address
                    $addressMethod = '/crm.requisite.address.list';
                    $addressData = [
                        'filter' => [
                            // 'ENTITY_TYPE_ID' => 4,
                            'ENTITY_ID' =>  $rqId,
                        ]
                    ];
                    $url = $hook . $addressMethod;
                    $responseData = Http::post($url,  $addressData);
                    $result['client']['address']  = BitrixController::getBitrixResponse($responseData, $addressMethod);
                }
            }


            return APIController::getSuccess(
                ['init' => $result,]
            );
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                ['companyId' => $companyId, 'domain' => $domain]
            );
        }
    }

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
                    $resultContract = new PortalContractResource($portalcontract);



                    if (!empty($portalcontract['contract'])) {


                        // if (empty($portalcontract['productName'])) {
                        //     $resultContract['productName'] = $portalcontract['contract']['productName'];
                        // }
                        // if (!empty($portalcontract['portal_measure'])) {

                        //     if (!empty($portalcontract['portal_measure']['bitrixId'])) {

                        //         $resultContract['bitrixMeasureId'] = (int)$portalcontract['portal_measure']['bitrixId'];
                        //     }
                        // }
                    }

                    // $fieldItem = BitrixfieldItem::find($portalcontract['bitrixfield_item_id']);
                    // $field = Bitrixfield::find($fieldItem['bitrixfield_id']);


                    // $portalcontract['code'] = $portalcontract['contract']['code'];
                    // $portalcontract['shortName'] = $portalcontract['contract']['code'];
                    // $portalcontract['number'] = $portalcontract['contract']['number'];


                    // $resultContract['fieldItem'] = $fieldItem;
                    // $resultContract['field'] = $field;
                    // $portalcontract['aprilName'] =  $portalcontract;
                    // $resultContract['bitrixName'] =  $fieldItem['title'];
                    // $portalcontract['discount'] = (int)$portalcontract['contract']['discount'];
                    // $portalcontract['prepayment'] = (int)$portalcontract['contract']['prepayment'];

                    // $resultContract['itemId'] =  $fieldItem['bitrixId'];

                    array_push($resultContracts, $resultContract);
                }




                return APIController::getSuccess(
                    ['contracts' => $resultContracts]
                );
            }
        }


        return APIController::getSuccess(
            ['contracts' => $portalcontracts, 'portal' => $portal]
        );
    }
}
