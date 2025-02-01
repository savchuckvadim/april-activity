<?php

namespace App\Http\Controllers\Admin\Garant;

use App\Http\Controllers\APIController;
use App\Http\Controllers\Controller;
use App\Models\Garant\Complect;
use Illuminate\Http\Request;

class ComplectController extends Controller
{
    public static function getInitial()
    {

        $initialData = Complect::getForm();
        $data = [
            'initial' => $initialData
        ];
        return APIController::getSuccess($data);
    }


    public static function store(Request $request)
    {
        // $id = null;
        // $portal = null;
        // if (isset($request['id'])) {
        //     $id = $request['id'];
        //     $smart = Smart::find($id);
        // } else {
        //     if (isset($request['portal_id'])) {

        //         $portal_id = $request['portal_id'];
        //         $portal = Portal::find($portal_id);
        //         $smart = new Smart();
        //         $smart->portal_id = $portal_id;
        //     }
        // }
        $request->merge([
            'withABS' => filter_var($request->input('withABS'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            'withConsalting' => filter_var($request->input('withConsalting'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            'withServices' => filter_var($request->input('withServices'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            'withLt' => filter_var($request->input('withLt'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            'isChanging' => filter_var($request->input('isChanging'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            'withDefault' => filter_var($request->input('withDefault'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
        ]);

        $validatedData = $request->validate([
            'id' => 'sometimes|integer|exists:complects,id',
            // 'entity_type' => 'required|string',
            'name' => 'required|string',
            'fullName' => 'required|string',
            'shortName' => 'required|string',
            'description' => 'sometimes|nullable|string',
            'code' => 'required|string',
            // 'code' => 'required|string',
            'type' => 'required|string',
            'color' => 'sometimes|nullable|string',
            'weight' => 'required|string',
            'abs' => 'sometimes|nullable|string',
            'number' => 'required|string',
            'productType' => 'required|string',
            'withABS' => 'required|boolean',
            'withConsalting' => 'required|boolean',
            'withServices' => 'required|boolean',
            'withLt' => 'required|boolean',
            'isChanging' => 'required|boolean',
            'withDefault' => 'required|boolean',
        ]);
        $currentComplect =  null;
        if(!empty($validatedData['id'])){
            $currentComplect = Complect::find($validatedData['id']);
        }
      
        if(empty($currentComplect)){
            $currentComplect = new Complect($validatedData);

        }

        // if ($smart) {
        //     // Создание нового Counter


        //     $smart->name = $name;
        //     $smart->title = $title;
        //     $smart->type = $type;
        //     $smart->group = $group;
        //     $smart->bitrixId = $bitrixId;
        //     $smart->entityTypeId = $entityTypeId;
        //     $smart->forStageId = $forStageId;
        //     $smart->forFilterId = $forFilterId;
        //     $smart->crmId = $crmId;
        //     $smart->forStage = $forStage;
        //     $smart->forFilter = $forFilter;
        //     $smart->crm = $crm;

        //     $smart->save(); // Сохранение Counter в базе данных

        //     return APIController::getSuccess(
        //         ['smart' => $smart, 'portal' => $portal]
        //     );
        // }

        return APIController::getError(
            'portal was not found',
            ['complect' => '']

        );
    }
    public static function get($smartId)
    {
        try {
            // $smart = Smart::find($smartId);

            // if ($smart) {
            //     $resultSmart = new SmartResource($smart);
            //     return APIController::getSuccess(
            //         ['smart' => $resultSmart]
            //     );
            // } else {
            //     return APIController::getError(
            //         'smart was not found',
            //         ['smart' => $smart]
            //     );
            // }
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                ['smartId' => $smartId]
            );
        }
    }
    // public static function getAll()
    // {

    //     // Создание нового Counter
    //     $smarts = Smart::all();
    //     if ($smarts) {

    //         return APIController::getSuccess(
    //             ['smarts' => $smarts]
    //         );
    //     }


    //     return APIController::getError(
    //         'callingGroups was not found',
    //         null

    //     );
    // }
}
