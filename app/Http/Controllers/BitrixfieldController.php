<?php

namespace App\Http\Controllers;

use App\Models\Bitrixfield;
use App\Models\Bitrixlist;
use Illuminate\Http\Request;

class BitrixfieldController extends Controller
{

    public static function getInitial($parentId = null)
    {

        $initialData = Bitrixfield::getForm($parentId);
        $data = [
            'initial' => $initialData
        ];
        return APIController::getSuccess($data);
    }

    public function set(Request $request)
    {
       
        $parent = null;
        $fieldData = [
            'title' => $request['title'],
            'name' => $request['name'],
            'code' => $request['code'],
            'type' => $request['type'], //Тип филда (select, date, string)
            'entityType' => $request['entityType'],  // тип родителя - чтобы контроллер от этого условия определил нужную модель родителя
            'entity_id' => (int)$request['entity_id'],  // id сущности родителя, тип родителя определяется на сервере 
           
            'parent_type' => $request['parent_type'],   //принадлежность филда к родительской модели list complectField для доступа из родителя к определенного типа филдам в сделках - только для товаров например
            'bitrixId' => $request['bitrixId'],  
            'bitrixCamelId' => $request['bitrixCamelId'],  
        ];


        if($fieldData['entityType'] == 'list'){
            $parent = Bitrixlist::class;

        }

        $field = new Bitrixfield([
            'entity_type' => $parent,
            'entity_id' => $request->entity_id,
            'parent_type' => $request->parent_type,
            'type' => $request->type,
            'title' => $request->title,
            'name' => $request->name,
            'code' => $request->code,
            'bitrixId' => $request->bitrixId,
            'bitrixCamelId' => $request->bitrixCamelId
        ]);

        $field->save();
    }
    // public static function getInitial($portalId)
    // {

    //     $initialData = Smart::getForm($portalId);
    //     $data = [
    //         'initial' => $initialData
    //     ];
    //     return APIController::getSuccess($data);
    // }


    // public static function set(Request $request)
    // {
    //     $type = $request['type'];
    //     $group = $request['group'];
    //     $name = $request['name'];
    //     $title = $request['title'];
    //     $bitrixId = $request['bitrixId'];
    //     $entityTypeId = $request['entityTypeId'];
    //     $forStageId = $request['forStageId'];
    //     $forFilterId = $request['forFilterId'];
    //     $crmId = $request['crmId'];
    
    //     $forStage = $request['forStage'];
    //     $forFilter = $request['forFilter'];
    //     $crm = $request['crm'];
    //     $portal_id = $request['portal_id'];


    //     $portal = Portal::find($portal_id);

    //     if ($portal) {
    //         // Создание нового Counter
    //         $smart = new Smart();

    //         $smart->name = $name;
    //         $smart->title = $title;
    //         $smart->type = $type;
    //         $smart->group = $group;
    //         $smart->bitrixId = $bitrixId;
    //         $smart->entityTypeId = $entityTypeId;
    //         $smart->forStageId = $forStageId;
    //         $smart->forFilterId = $forFilterId;
    //         $smart->crmId = $crmId;
    //         $smart->forStage = $forStage;
    //         $smart->forFilter = $forFilter;
    //         $smart->crm = $crm;
    //         $smart->portal_id = $portal_id;
    //         $smart->save(); // Сохранение Counter в базе данных

    //         return APIController::getSuccess(
    //             ['smart' => $smart, 'portal' => $portal]
    //         );
    //     }

    //     return APIController::getError(
    //         'portal was not found',
    //         ['portal' => $portal]

    //     );
    // }
    // public static function get($smartId)
    // {
    //     try {
    //         $smart = Smart::find($smartId);
    //         if ($smart) {

    //             return APIController::getSuccess(
    //                 ['smart' => $smart]
    //             );
    //         } else {
    //             return APIController::getError(
    //                 'smart was not found',
    //                 ['smart' => $smart]
    //             );
    //         }
    //     } catch (\Throwable $th) {
    //         return APIController::getError(
    //             $th->getMessage(),
    //             ['smartId' => $smartId]
    //         );
    //     }
    // }
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

    // public static function delete($smartId)
    // {
    //     $smart = Smart::find($smartId);

    //     if ($smart) {
    //         // Получаем все связанные поля
    //         $smart->delete();
    //         return APIController::getSuccess($smart);
    //     } else {

    //         return APIController::getError(
    //             'Smart not found',
    //             null
    //         );
    //     }
    // }
}
