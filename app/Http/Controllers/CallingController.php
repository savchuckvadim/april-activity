<?php

namespace App\Http\Controllers;

use App\Models\Calling;
use App\Models\Portal;
use Illuminate\Http\Request;

class CallingController extends Controller
{
    public static function getInitial($portalId = null)
    {

        $initialData = Calling::getForm($portalId);
        $data = [
            'initial' => $initialData
        ];
        return APIController::getSuccess($data);
    }

    public static function set(Request $request)
    {
        // Предполагая, что у вас уже есть экземпляр Template
        $type = $request['type'];
        $group = $request['group'];
        $name = $request['name'];
        $title = $request['title'];
        $bitrixId = $request['bitrixId'];
        $portal_id = $request['portal_id'];


        $portal = Portal::find($portal_id);

        if ($portal) {
            // Создание нового Counter
            $callingGroup = new Calling;
            if ($callingGroup) {

                $callingGroup->name = $name;
                $callingGroup->title = $title;
                $callingGroup->type = $type;
                $callingGroup->group = $group;
                $callingGroup->bitrixId = $bitrixId;
                $callingGroup->portal_id = $portal_id;
                $callingGroup->save(); // Сохранение Counter в базе данных

                return APIController::getSuccess(
                    ['callingGroup' => $callingGroup, 'portal' => $portal]
                );
            }
        }

        return APIController::getError(
            'template or counter was not found',
            ['portal' => $portal]

        );
    }
    public static function getAll()
    {

        // Создание нового Counter
        $callingGroups = Calling::all();
        if ($callingGroups) {

            return APIController::getSuccess(
                ['callingGroups' => $callingGroups]
            );
        }


        return APIController::getError(
            'callingGroups was not found',
            null

        );
    }

    public static function getCallingGroup($groupId)
    {
        try {
            $callingGroup = Calling::find($groupId);
            if ($callingGroup) {

                return APIController::getSuccess(
                    ['callingGroup' => $callingGroup]
                );
            } else {
                return APIController::getError(
                    'field was not found',
                    ['callingGroup' => $callingGroup]
                );
            }
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                ['callingGroup' => $callingGroup]
            );
        }
    }
}
