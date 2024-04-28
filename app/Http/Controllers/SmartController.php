<?php

namespace App\Http\Controllers;

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


    public static function set(Request $request)
    {
        $type = $request['type'];
        $group = $request['group'];
        $name = $request['name'];
        $title = $request['title'];
        $bitrixId = $request['bitrixId'];
        $entityTypeId = $request['entityTypeId'];
        $forStageId = $request['forStageId'];
        $forFilterId = $request['forFilterId'];
        $crmId = $request['crmId'];
    
        $forStage = $request['forStage'];
        $forFilter = $request['forFilter'];
        $crm = $request['crm'];
        $portal_id = $request['portal_id'];


        $portal = Portal::find($portal_id);

        if ($portal) {
            // Создание нового Counter
            $smart = new Smart();

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
            $smart->portal_id = $portal_id;
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

                return APIController::getSuccess(
                    ['smart' => $smart]
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
}
