<?php

namespace App\Http\Controllers;

use App\Models\Bitrixfield;
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
        //entityType logo | stamp | последняя или единственная часть урл
        // $parentType - может быть null тогда можно взять из formdata // вообще это название родительской модели из url
        // parentId - id родительского элемента
        $fieldData = [
            'name' => $request['name'],
            'code' => $request['code'],
            'type' => $request['type'], //Тип файла (table | video | word | img)
            // 'parent' => $request['parent'], //Родительская модель (rq)
            'parent_type' => $request['parent_type'], //Название файла в родительской модели logo stamp  // rq->morphMany(File::class, 'entity')->where('parent_type', 'logo'); //тоже системное поле по которому rq определяет что за связь - logo | stamp | signature по сути название типа файла
            // 'availability' => $request['availability'], //Доступность public |  local
            // '$entityType' => $entityType, // file->entity_type - системное поле для осуществления полиморфной связи с родительской моделью
            // '$parentType' => $parentType,
            // '$parentId' => $parentId,
            // 'file' => $request['file'],

        ];
        $request->validate([
            // 'entity_type' => 'required|string',
            'entity_id' => 'required|integer',
            'parent_type' => 'required|string',
            'type' => 'required|string',
            'title' => 'required|string',
            'name' => 'required|string',
            'code' => 'required|string',
            'bitrixId' => 'required|string',
            'bitrixCamelId' => 'required|string'
        ]);

        $field = new Bitrixfield([
            'entity_type' => $request->entity_type,
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
