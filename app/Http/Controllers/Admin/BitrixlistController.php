<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\APIController;
use App\Http\Controllers\Controller;
use App\Http\Resources\BitrixlistResource;
use App\Models\Bitrixlist;
use App\Models\Portal;
use Illuminate\Http\Request;

class BitrixlistController extends Controller
{
    public static function getInitial($parentId = null)
    {

        $initialData = Bitrixlist::getForm($parentId);
        $data = [
            'initial' => $initialData,
            'parentId' => $parentId
        ];
        return APIController::getSuccess($data);
    }


    public static function get($bitrixlistId)
    {
        try {
            $bitrixlist = Bitrixlist::find($bitrixlistId);
            $bitrixlist = new BitrixlistResource($bitrixlist);
            if ($bitrixlist) {

                return APIController::getSuccess(
                    ['bitrixlist' => $bitrixlist]
                );
            } else {
                return APIController::getError(
                    'bitrixlist was not found',
                    ['bitrixlist' => $bitrixlist]
                );
            }
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                ['bitrixlistId' => $bitrixlistId]
            );
        }
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
            $bitrixlist = new Bitrixlist();

            $bitrixlist->name = $name;
            $bitrixlist->title = $title;
            $bitrixlist->type = $type;
            $bitrixlist->group = $group;
            $bitrixlist->bitrixId = $bitrixId;
            $bitrixlist->portal_id = $portal_id;
            $bitrixlist->save(); // Сохранение Counter в базе данных

            return APIController::getSuccess(
                ['bitrixlist' => $bitrixlist, 'portal' => $portal]
            );
        }

        return APIController::getError(
            'portal or bitrixlist  was not found',
            ['portal' => $portal]

        );
    }

    public static function getAll()
    {

        // Создание нового Counter
        $bitrixlists = Bitrixlist::all();
        if ($bitrixlists) {

            return APIController::getSuccess(
                ['bitrixlists' => $bitrixlists]
            );
        }


        return APIController::getError(
            'callingGroups was not found',
            null

        );
    }


    public static function getFields($bitrixlistId)
    {
        try {
            $bitrixlist = Bitrixlist::find($bitrixlistId);
            $bitrixlistfields = $bitrixlist->fields;
            if ($bitrixlist) {

                return APIController::getSuccess(
                    ['bitrixfields' => $bitrixlistfields]
                );
            } else {
                return APIController::getError(
                    'bitrixlist was not found',
                    ['bitrixfields' => $bitrixlistfields]
                );
            }
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                ['bitrixlistId' => $bitrixlistId]
            );
        }
    }

    
}
