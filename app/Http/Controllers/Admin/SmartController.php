<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\APIController;
use App\Http\Controllers\Controller;
use App\Http\Resources\SmartResource;
use App\Models\Portal;
use App\Models\Smart;
use Illuminate\Http\Request;

class SmartController extends Controller
{
    public static function getInitial($portalId = null)
    {

        $initialData = Smart::getForm($portalId);
        $data = [
            'initial' => $initialData
        ];
        return APIController::getSuccess($data);
    }


    public static function store(Request $request)
    {
        $id = null;
        $portal = null;
        if (isset($request['id'])) {
            $id = $request['id'];
            $smart = Smart::find($id);
        } else {
            if (isset($request['portal_id'])) {

                $portal_id = $request['portal_id'];
                $portal = Portal::find($portal_id);
                $smart = new Smart();
                $smart->portal_id = $portal_id;
            }
        }
        $validatedData = $request->validate([
            'id' => 'sometimes|integer|exists:smarts,id',
            // 'entity_type' => 'required|string',
            'type' => 'required|string',
            'group' => 'required|string',
            'name' => 'required|string',
            'title' => 'required|string',
            'entityTypeId' => 'required|string',
            // 'code' => 'required|string',
            'bitrixId' => 'required|string',
            'forStageId' => 'required|string',
            'forFilterId' => 'required|string',
            'crmId' => 'required|string',
            'forStage' => 'required|string',
            'forFilter' => 'required|string',
            'crm' => 'required|string',
        ]);
        $type = $validatedData['type'];
        $group = $validatedData['group'];
        $name = $validatedData['name'];
        $title = $validatedData['title'];
        $bitrixId = $validatedData['bitrixId'];
        $entityTypeId = $validatedData['entityTypeId'];
        $forStageId = $validatedData['forStageId'];
        $forFilterId = $validatedData['forFilterId'];
        $crmId = $validatedData['crmId'];

        $forStage = $request['forStage'];
        $forFilter = $validatedData['forFilter'];
        $crm = $validatedData['crm'];


        if ($smart) {
            // Создание нового Counter


            $smart->name = $name;
            $smart->title = $title;
            $smart->type = $type;
            $smart->group = $group;
            $smart->bitrixId = $bitrixId;
            $smart->entityTypeId = $entityTypeId;
            $smart->forStageId = $forStageId;
            $smart->forFilterId = $forFilterId;
            $smart->crmId = $crmId;
            $smart->forStage = $forStage;
            $smart->forFilter = $forFilter;
            $smart->crm = $crm;

            $smart->save(); // Сохранение Counter в базе данных

            return APIController::getSuccess(
                ['smart' => $smart, 'portal' => $portal]
            );
        }

        return APIController::getError(
            'portal was not found',
            ['portal' => $portal]

        );
    }
    public static function get($smartId)
    {
        try {
            $smart = Smart::find($smartId);

            if ($smart) {
                $resultSmart = new SmartResource($smart);
                return APIController::getSuccess(
                    ['smart' => $resultSmart]
                );
            } else {
                return APIController::getError(
                    'smart was not found',
                    ['smart' => $smart]
                );
            }
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                ['smartId' => $smartId]
            );
        }
    }
    public static function getAll()
    {

        // Создание нового Counter
        $smarts = Smart::all();
        if ($smarts) {

            return APIController::getSuccess(
                ['smarts' => $smarts]
            );
        }


        return APIController::getError(
            'callingGroups was not found',
            null

        );
    }

    public static function delete($smartId)
    {
        $smart = Smart::find($smartId);

        if ($smart) {
            // Получаем все связанные поля
            $smart->delete();
            return APIController::getSuccess($smart);
        } else {

            return APIController::getError(
                'Smart not found',
                null
            );
        }
    }

    public static function getByPortal($portalId)
    {

        // Создание нового Counter
        $portal = Portal::find($portalId);
        $smarts = $portal->smarts;
        if ($smarts) {

            return APIController::getSuccess(
                ['smarts' => $smarts]
            );
        }


        return APIController::getError(
            'smarts was not found',
            ['portal id' => $portalId]

        );
    }
    public static function getCategories($smartId)
    {

        try {
            $smart = Smart::find($smartId);

            if ($smart) {
                $categories = $smart->categories;
                return APIController::getSuccess(
                    ['categories' => $categories]
                );
            } else {
                return APIController::getError(
                    'smart was not found',
                    ['smart' => $smart]
                );
            }
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                ['smartId' => $smartId]
            );
        }
    }

    public static function getFields($smartId)
    {

        try {
            $smart = Smart::find($smartId);

            if ($smart) {
                $bitrixfields = $smart->fields;
                return APIController::getSuccess(
                    ['bitrixfields' => $bitrixfields]
                );
            } else {
                return APIController::getError(
                    'smart was not found',
                    ['smart' => $smart]
                );
            }
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                ['smartId' => $smartId]
            );
        }
    }
}
