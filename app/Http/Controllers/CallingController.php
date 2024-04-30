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
    public static function store(Request $request)
    {
        $id = null;
        $portal = null;
  
        $validatedData = $request->validate([
            'id' => 'sometimes|integer|exists:smarts,id',
            // 'entity_type' => 'required|string',
            'type' => 'required|string',
            'group' => 'required|string',
            'name' => 'required|string',
            'title' => 'required|string',
            // 'entityTypeId' => 'required|string',
            // 'code' => 'required|string',
            'bitrixId' => 'required|string',
            'portal_id' => 'required|string',
            
        ]);

        if (isset($request['id'])) {
            $id = $request['id'];
            $callingGroup = Calling::find($id);
        } else {
            if (isset($request['portal_id'])) {

                $portal_id = $request['portal_id'];
                $portal = Portal::find($portal_id);
                $callingGroup = new Calling();
                $callingGroup->portal_id = $portal_id;
            }
        }
        
        $type = $validatedData['type'];
        $group = $validatedData['group'];
        $name = $validatedData['name'];
        $title = $validatedData['title'];
        $bitrixId = $validatedData['bitrixId'];
        $portal_id = $validatedData['portal_id'];


        if ($callingGroup) {
            // Создание нового Counter


            $callingGroup->name = $name;
            $callingGroup->type = $type;
            $callingGroup->group = $group;
            $callingGroup->title = $title;
            $callingGroup->bitrixId = $bitrixId;

            $callingGroup->save(); // Сохранение Counter в базе данных

            return APIController::getSuccess(
                ['callingGroup' => $callingGroup, 'portal' => $portal]
            );
        }

        return APIController::getError(
            'portal was not found',
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

    public static function get($groupId)
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

    public static function getByPortal($portalId)
    {

        // Создание нового Counter
        $portal = Portal::find($portalId);
        $callingGroups = $portal->callingGroups;
        if ($callingGroups) {

            return APIController::getSuccess(
                ['callingGroups' => $callingGroups]
            );
        }


        return APIController::getError(
            'callingGroups was not found',
            ['portal id' => $portalId]

        );
    }
}
