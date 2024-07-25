<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\APIController;
use App\Http\Controllers\Controller;
use App\Models\Portal;
use App\Models\PortalContract;
use Illuminate\Http\Request;

class PortalContractController extends Controller
{
    public static function getInitial($portalId = null)
    {

        $initialData = PortalContract::getForm($portalId);
        $data = [
            'initial' => $initialData
        ];
        return APIController::getSuccess($data);
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $id = null;
        $portal = null;
        $measure = null;

        if (isset($request['id'])) {
            $id = $request['id'];
            $portalContract = PortalContract::find($id);
        } else {
            $portalContract = new PortalContract();
            if (isset($request['portal_id'])) {
                $portal_id = $request['portal_id'];

                $portalContract->portal_id = $portal_id;
                $portal = Portal::find($portal_id);
            }

            if (isset($request['contract_id'])) {

                $contract_id  = (int)$request['contract_id'];
                $portalContract->contract_id = $contract_id;
            }

            if (isset($request['portal_measure_id'])) {

                $portal_measure_id = (int)$request['portal_measure_id'];
                $portalContract->portal_measure_id = $portal_measure_id;
            }

            if (isset($request['bitrixfield_item_id'])) {

                $bitrixfield_item_id = (int)$request['bitrixfield_item_id'];
                $portalContract->bitrixfield_item_id = $bitrixfield_item_id;
            }
        }
        $validatedData = $request->validate([
            'id' => 'sometimes|integer|exists:portal_contract,id',
            // 'bitrixId' => 'required|string',
            'title' => 'required|string',
            'productName' => 'sometimes',
            'description' => 'sometimes',
            'order' => 'sometimes',


        ]);


        if ($portalContract) {
            // Создание нового Counter


            $portalContract->title = (string)$validatedData['title'];
            $portalContract->productName = (string)$validatedData['productName'];
            $portalContract->description = (string)$validatedData['description'];
            $portalContract->order = (int)$validatedData['order'];
      
            $portalContract->save(); // Сохранение Counter в базе данных

            return APIController::getSuccess(
                ['portalcontract' => $portalContract, 'portal' => $portal, 'measure' => $measure]
            );
        }

        return APIController::getError(
            'portalcontract was not found',
            ['rq' => $request]

        );
    }

    /**
     * Display the specified resource.
     */
    public function get($portalContractId)
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



    public function getByPortal($portalId)
    {
        $portalcontracts = [];
        // Создание нового Counter
        $portal = Portal::find($portalId);
        if ($portal) {
            $portalcontracts = $portal->contracts;
            if (($portalcontracts)) {

                return APIController::getSuccess(
                    ['portalcontracts' => $portalcontracts]
                );
            }
        }


        return APIController::getSuccess(
            ['portalcontracts' => $portalcontracts]
        );
    }
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
