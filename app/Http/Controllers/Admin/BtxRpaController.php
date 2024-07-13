<?php

namespace App\Http\Controllers;

use App\Models\BtxRpa;
use App\Models\Portal;
use Illuminate\Http\Request;

class BtxRpaController extends Controller
{
    public static function getInitial($portalId = null)
    {

        $initialData = BtxRpa::getForm($portalId);
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
            $rpa = BtxRpa::find($id);
        } else {
            if (isset($request['portal_id'])) {

                $portal_id = $request['portal_id'];
                $portal = Portal::find($portal_id);
                $rpa = new BtxRpa();
                $rpa->portal_id = $portal_id;
            }
        }
        $validatedData = $request->validate([
            'id' => 'sometimes|integer|exists:btx_rpas,id',
            // 'entity_type' => 'required|string',
            'type' => 'required|string',
            'group' => 'required|string',
            'name' => 'required|string',
            'title' => 'required|string',
            'code' => 'required|string',
            'entityTypeId' => 'required|string',
            // 'code' => 'required|string',
            'bitrixId' => 'sometimes|string',
            'forStageId' => 'sometimes|string',
            'forFilterId' => 'sometimes|string',
            'crmId' => 'sometimes|string',
            'typeId' => 'required|string',
            'description' => 'sometimes|string',
  
        ]);
        $type = $validatedData['type'];
        $code = $validatedData['code'];
        $group = $validatedData['group'];
        $name = $validatedData['name'];
        $title = $validatedData['title'];
        $bitrixId = $validatedData['bitrixId'];
        $entityTypeId = $validatedData['entityTypeId'];
        $forStageId = $validatedData['forStageId'];
        $forFilterId = $validatedData['forFilterId'];
        $crmId = $validatedData['crmId'];

        $typeId = $request['typeId'];
        $description = $validatedData['description'];


        if ($rpa) {
            // Создание нового Counter


            $rpa->name = $name;
            $rpa->title = $title;
            $rpa->type = $type;
            $rpa->code = $code;
            $rpa->group = $group;
            $rpa->bitrixId = $bitrixId;
            $rpa->entityTypeId = $entityTypeId;
            $rpa->forStageId = $forStageId;
            $rpa->forFilterId = $forFilterId;
            $rpa->crmId = $crmId;

            $rpa->typeId = $typeId;
            $rpa->description = $description;
         

            $rpa->save(); // Сохранение Counter в базе данных

            return APIController::getSuccess(
                ['rpa' => $rpa, 'portal' => $portal]
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
    public function get($btxRpaId)
    {
        try {
            $rpa = BtxRpa::find($btxRpaId);

            if ($rpa) {
                // $resultSmart = new SmartResource($rpa);
                return APIController::getSuccess(
                    ['rpa' => $rpa]
                );
            } else {
                return APIController::getError(
                    'smart was not found',
                    ['rpa' => $rpa]
                );
            }
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                ['btxRpaId' => $btxRpaId]
            );
        }
    }

    public  function getAll()
    {

        // Создание нового Counter
        $rpas = BtxRpa::all();
        if ($rpas) {

            return APIController::getSuccess(
                ['rpas' => $rpas]
            );
        }


        return APIController::getError(
            'callingGroups was not found',
            null

        );
    }

    public function getByPortal($portalId)
    {

        // Создание нового Counter
        $portal = Portal::find($portalId);
        $rpas = $portal->rpas;
        if ($rpas) {

            return APIController::getSuccess(
                ['rpas' => $rpas]
            );
        }


        return APIController::getError(
            'rpas was not found',
            ['portal id' => $portalId]

        );
    }
    /**
     * Show the form for editing the specified resource.
     */
    public static function getCategories($btxRpaId)
    {

        try {
            $rpa = BtxRpa::find($btxRpaId);

            if ($rpa) {
                $categories = $rpa->categories;
                return APIController::getSuccess(
                    ['categories' => $categories]
                );
            } else {
                return APIController::getError(
                    'smart was not found',
                    ['rpa' => $rpa]
                );
            }
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                ['btxRpaId' => $btxRpaId]
            );
        }
    }

    public  function getFields($btxRpaId)
    {

        try {
            $rpa = BtxRpa::find($btxRpaId);

            if ($rpa) {
                $bitrixfields = $rpa->fields;
                return APIController::getSuccess(
                    ['bitrixfields' => $bitrixfields]
                );
            } else {
                return APIController::getError(
                    'smart was not found',
                    ['rpa' => $rpa]
                );
            }
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                ['btxRpaId' => $btxRpaId]
            );
        }
    }
    public function destroy($btxRpaId)
    {
        $rpa = BtxRpa::find($btxRpaId);

        if ($rpa) {
            // Получаем все связанные поля
            $rpa->delete();
            return APIController::getSuccess($rpa);
        } else {

            return APIController::getError(
                'Smart not found',
                null
            );
        }
    }
}
