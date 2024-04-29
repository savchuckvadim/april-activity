<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\APIController;
use App\Http\Controllers\Controller;
use App\Models\BtxStage;
use Illuminate\Http\Request;
use Mockery\Undefined;

class BtxStageController extends Controller
{
    public static function getInitial($categoryId = null)
    {

        $initialData = BtxStage::getForm($categoryId);
        $data = [
            'initial' => $initialData
        ];
        return APIController::getSuccess($data);
    }

    public static function store(Request $request)
    {
        $id = null;


        $validatedData = $request->validate([
            'id' => 'sometimes|integer|exists:btx_stages,id',
            'title' => 'required|string',
            'name' => 'required|string',
            'code' => 'required|string',
            'bitrixId' => 'required|string',
            'isActive' => 'required|string',
            'btx_category_id' => 'required|string',
            'color' => 'sometimes|string',


        ]);

        if (isset($validatedData['id'])) {
            $id = $validatedData['id'];
            $currentStage = BtxStage::find($id);
        } else {

            $currentStage = new BtxStage();
        }

        $color = ' ';
        if (isset($validatedData['color'])) {
            $color = '#isset';
            if (!$validatedData['color'] || $validatedData['color'] == '' || $validatedData['color'] == null || $validatedData['color'] == 'null') {

                $color = '#00000';
            }
        }


        if ($validatedData['isActive'] == 'true' || $validatedData['isActive'] == '1') {
            $validatedData['isActive'] = true;
        } else if ($validatedData['isActive'] == 'false' || $validatedData['isActive'] == '0' || $validatedData['isActive'] == '') {
            $validatedData['isActive'] = false;
        }

        if ($currentStage) {
            // Создание нового Counter


            $currentStage->title = $validatedData['title'];
            $currentStage->name = $validatedData['name'];

            $currentStage->code = $validatedData['code'];
            $currentStage->bitrixId = $validatedData['bitrixId'];
            $currentStage->color = $color;
            $currentStage->btx_category_id = $validatedData['btx_category_id'];
            $currentStage->isActive = $validatedData['isActive'];

            $currentStage->save(); // Сохранение Counter в базе данных

            return APIController::getSuccess(
                ['stage' => $currentStage, '$validatedData' => $validatedData]
            );
        }

        return APIController::getError(
            'portal was not found',
            ['stage' => $currentStage]

        );
    }

    public static function get($stageId)
    {

        $stage = BtxStage::find($stageId);
        $data = [
            'stage' => $stage
        ];
        if ($stage) {
            // $resultCategory = new BtxCategoryResource($category);
            // $data = [
            //     'category' => $resultCategory
            // ];

            return APIController::getSuccess($data);
        }

        return APIController::getError('category was not found', $data);
    }

    public static function delete($stageId)
    {

        $stage = BtxStage::find($stageId);
        $data = [
            'stage' => $stage
        ];
        if ($stage) {
            $stage->delete();
            $data = [
                'stage' => $stage
            ];
            return APIController::getSuccess($data);
        }

        return APIController::getError('stage was not found', ['categoryId' => $stageId]);
    }
}
