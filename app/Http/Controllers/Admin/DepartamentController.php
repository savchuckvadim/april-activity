<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\APIController;
use App\Http\Controllers\Controller;
use App\Models\Departament;
use App\Models\Portal;
use Bitrix24\Departments\Department;
use Illuminate\Http\Request;

class DepartamentController extends Controller
{
    public static function getInitial($portalId = null)
    {

        $initialData = Departament::getForm($portalId);
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
        $portal_id = $request['portal_id'];


        $portal = Portal::find($portal_id);

        if ($portal) {
            // Создание нового Counter
            $departament = new Departament;

            $departament->name = $name;
            $departament->title = $title;
            $departament->type = $type;
            $departament->group = $group;
            $departament->bitrixId = $bitrixId;
            $departament->portal_id = $portal_id;
            $departament->save(); // Сохранение Counter в базе данных

            return APIController::getSuccess(
                ['departament' => $departament, 'portal' => $portal]
            );
        }

        return APIController::getError(
            'portal was not found',
            ['portal' => $portal]

        );
    }

    public static function get($departamentId)
    {
        try {
            $departament = Departament::find($departamentId);

            if ($departament) {

                return APIController::getSuccess(
                    ['departament' => $departament]
                );
            } else {
                return APIController::getError(
                    'departament was not found',
                    ['departament' => $departament]
                );
            }
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                ['departamentId' => $departamentId]
            );
        }
    }

    public static function delete($departamentId)
    {
        $departament = Departament::find($departamentId);

        if ($departament) {
            // Получаем все связанные поля
            $departament->delete();
            return APIController::getSuccess($departament);
        } else {

            return APIController::getError(
                'departament not found',
                null
            );
        }
    }


    public static function getAll()
    {

        // Создание нового Counter
        $departaments = Departament::all();
        if ($departaments) {

            return APIController::getSuccess(
                ['departaments' => $departaments]
            );
        }


        return APIController::getError(
            'callingGroups was not found',
            null

        );
    }
}
