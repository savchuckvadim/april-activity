<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\APIController;
use App\Http\Controllers\Controller;
use App\Models\BtxCategory;
use App\Models\Smart;
use Illuminate\Http\Request;

class BtxCategoryController extends Controller
{
    public static function getInitial($parentId = null, $parentType)
    {

        $initialData = BtxCategory::getForm($parentId, $parentType);
        $data = [
            'initial' => $initialData
        ];
        return APIController::getSuccess($data);
    }

    public static function store(Request $request)
    {
        $id = null;
      
        if (isset($request['id'])) {
            $id = $request['id'];
            $currentCategory = BtxCategory::find($id);
        } else {
            // if (isset($request['portal_id'])) {

            //     $portal_id = $request['portal_id'];
            //     $portal = Portal::find($portal_id);
            //     $smart = new Smart();
            //     $smart->portal_id = $portal_id;
            // }
            $currentCategory = new BtxCategory();
        }
        $validatedData = $request->validate([
            'id' => 'sometimes|integer|exists:smarts,id',
            'type' => 'required|string',
            'group' => 'required|string',
            'name' => 'required|string',
            'title' => 'required|string',
            'entity_type' => 'required|string',
            'entity_id' => 'required|string',
            'parent_type' => 'required|string',
            'code' => 'required|string',
            'bitrixId' => 'required|string',
            'bitrixCamelId' => 'required|string',

        ]);



        if ($validatedData['entity_type'] === 'smart') {
        
            $validatedData['entity_type'] = Smart::class;
        } else if ($validatedData['entity_type'] === 'deal') {
        }else if ($validatedData['entity_type'] === 'lead') {
        }else if ($validatedData['entity_type'] === 'task') {
        }

        if ($currentCategory) {
            // Создание нового Counter


            $currentCategory->type = $validatedData['type'];
            $currentCategory->group = $validatedData['group'];
            $currentCategory->title = $validatedData['title'];
            $currentCategory->name = $validatedData['name'];
            $currentCategory->code = $validatedData['code'];
            $currentCategory->bitrixId = $validatedData['bitrixId'];
            $currentCategory->bitrixCamelId = $validatedData['bitrixCamelId'];

            $currentCategory->entity_id = $validatedData['entity_id'];
            $currentCategory->entity_type = $validatedData['entity_type'];
            $currentCategory->parent_type = $validatedData['parent_type'];

            $currentCategory->save(); // Сохранение Counter в базе данных

            return APIController::getSuccess(
                ['category' => $validatedData]
            );
        }

        return APIController::getError(
            'portal was not found',
            ['category' => $currentCategory,]

        );
    }
}
