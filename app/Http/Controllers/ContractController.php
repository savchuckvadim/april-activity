<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Measure;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    public static function getInitial()
    {

        $initialData = Contract::getForm();
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
        if (isset($request['id'])) {
            $id = $request['id'];
            $contract = Contract::find($id);
        } else {
            // if (isset($request['portal_id'])) {

                $contract = new Contract();
            // }
        }
        $validatedData = $request->validate([
            'id' => 'sometimes|integer|exists:contracts,id',
            // 'entity_type' => 'required|string',
            'name' => 'required|string',
      
            'number' => 'required|string',
            'title' => 'required|string',
            'code' => 'required|string',
            'type' => 'required|string',

            'productName' => 'required|string',
            'template' => 'sometimes',
            'order' => 'sometimes',
            'coefficient' => 'sometimes',
            'prepayment' => 'sometimes',
            'discount' => 'sometimes',
            'withPrepayment' => 'sometimes',
  
        ]);
        if ($contract) {
        $contract->type = $validatedData['type'];
        $contract->code = $validatedData['code'];
        $contract->name = $validatedData['name'];
        $contract->title = $validatedData['title'];
        $contract->number = (int)$validatedData['number'];
        $contract->coefficient = (int)$validatedData['coefficient'];
        $contract->prepayment = (int)$validatedData['prepayment'];
        $contract->discount = (int)$validatedData['discount'];
        $contract->template = $validatedData['template'];
        $contract->productName = $validatedData['productName'];
        $contract->withPrepayment = $validatedData['withPrepayment'];
      

            $contract->save(); // Сохранение Counter в базе данных

            return APIController::getSuccess(
                ['contract' => $contract, 'portal' => $portal]
            );
        }

        return APIController::getError(
            'portal was not found',
            ['portal' => $portal]

        );
    }

    /**
     * Display the specified resource.
     */
    public function get($contractId)
    {
        try {
            $contract = Contract::find($contractId);

            if ($contract) {
                // $resultSmart = new SmartResource($rpa);
                return APIController::getSuccess(
                    ['contract' => $contract]
                );
            } else {
                return APIController::getError(
                    'contract was not found',
                    ['contract' => $contract]
                );
            }
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                ['contractId' => $contractId]
            );
        }
    }

    public  function getAll()
    {

        // Создание нового Counter
        $contracts = Contract::all();
        if ($contracts) {

            return APIController::getSuccess(
                ['contracts' => $contracts]
            );
        }


        return APIController::getError(
            'measures was not found',
            null

        );
    }


    public function destroy($contractId)
    {
        $contract = Contract::find($contractId);

        if ($contract) {
            // Получаем все связанные поля
            $contract->delete();
            return APIController::getSuccess($contract);
        } else {

            return APIController::getError(
                'contract not found',
                null
            );
        }
    }
}
