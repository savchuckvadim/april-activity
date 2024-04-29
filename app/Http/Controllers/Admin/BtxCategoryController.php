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
        $parent = null;
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
            // 'entity_type' => 'required|string',
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
            // 'forFilterId' => 'required|string',
            // 'crmId' => 'required|string',
            // 'forStage' => 'required|string',
            // 'forFilter' => 'required|string',
            // 'crm' => 'required|string',
        ]);
        // $type = $validatedData['type'];
        // $group = $validatedData['group'];
        // $name = $validatedData['name'];
        // $title = $validatedData['title'];
        // $bitrixId = $validatedData['bitrixId'];
        // $entityTypeId = $validatedData['entityTypeId'];
        // $forStageId = $validatedData['forStageId'];
        // $forFilterId = $validatedData['forFilterId'];
        // $crmId = $validatedData['crmId'];

        // $forStage = $request['forStage'];
        // $forFilter = $validatedData['forFilter'];
        // $crm = $validatedData['crm'];

        if ($validatedData['entity_type'] === 'smart') {
            $validatedData['entity_type'] = 200;
            $validatedData['entity_type'] = Smart::class;
        } else if ($validatedData['entity_type'] === 'deal') {
        }else if ($validatedData['entity_type'] === 'lead') {
        }else if ($validatedData['entity_type'] === 'task') {
        }

        if ($currentCategory) {
            // Создание нового Counter


            // $smart->name = $name;
            // $smart->title = $title;
            // $smart->type = $type;
            // $smart->group = $group;
            // $smart->bitrixId = $bitrixId;
            // $smart->entityTypeId = $entityTypeId;
            // $smart->forStageId = $forStageId;
            // $smart->forFilterId = $forFilterId;
            // $smart->crmId = $crmId;
            // $smart->forStage = $forStage;
            // $smart->forFilter = $forFilter;
            // $smart->crm = $crm;

            // $smart->save(); // Сохранение Counter в базе данных

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
