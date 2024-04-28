<?php

namespace App\Http\Controllers;

use App\Models\Bitrixfield;
use App\Models\BitrixfieldItem;
use Illuminate\Http\Request;

class BitrixfieldItemController extends Controller
{
    public static function getInitial($parentId = null)
    {

        $initialData = BitrixfieldItem::getForm($parentId);
        $data = [
            'initial' => $initialData
        ];
        return APIController::getSuccess($data);
    }

    public static function store(Request $request)
    {

        $validatedData = $request->validate([
            'id' => 'sometimes|integer|exists:bitrixfields,id',
            'name' => 'required|integer',
            'title' => 'required|string',
            'code' => 'required|string',
            'bitrixfield_id' => 'required|string',
            'bitrixId' => 'required|string',

        ]);

        if (isset($validatedData['id'])) {
            // Попытка найти существующее поле
            $item = BitrixfieldItem::find($validatedData['id']);
            if (!$item) {
                return APIController::getError('Bitrix field Item not found', [], 404);
            }
        } else {
            // Создание нового поля, если ID не предоставлен
            $item = new BitrixfieldItem();
        }


        // Заполняем или обновляем поля модели

        $item->title = $validatedData['title'];
        $item->name = $validatedData['name'];
        $item->code = $validatedData['code'];
        $item->bitrixId = $validatedData['bitrixId'];
        $item->bitrixfield_id = $validatedData['bitrixfield_id'];

        $item->save();
        $responseData =   ['bitrixfielditem' => $item];

        if ($item) {
            return APIController::getSuccess($responseData);
        }
        return APIController::getError('btx field was not created', $responseData);
    }

    public static function get($bitrixfieldId)
    {

        $btxField = BitrixfieldItem::find($bitrixfieldId);


        return APIController::getSuccess(['bitrixlistfield' => $btxField]);
    }

    public static function delete($bitrixfieldId)
    {

        $btxField = BitrixfieldItem::find($bitrixfieldId);

        if ($btxField) {
            $btxField->delete();
            return APIController::getSuccess(['bitrixlistfield' => $btxField]);
        }
        return APIController::getError('btx field was not found and deleted', [
            'bitrixlistfield' => $btxField,
            // 'fieldData' => $fieldData
        ]);
    }

    public static function getFromField($bitrixfieldId)
    {

        $btxField = Bitrixfield::find($bitrixfieldId);
        $btxFieldItems = $btxField->items;

        return APIController::getSuccess(['bitrixlistfield' => $btxFieldItems]);
    }
}
