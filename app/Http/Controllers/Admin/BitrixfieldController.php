<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\APIController;
use App\Http\Controllers\Controller;
use App\Http\Resources\BitrixFieldResource;
use App\Models\Bitrixfield;
use App\Models\Bitrixlist;
use App\Models\BtxCompany;
use App\Models\BtxDeal;
use App\Models\BtxLead;
use App\Models\Smart;
use Illuminate\Http\Request;

class BitrixfieldController extends Controller
{

    public static function getInitial($parentId = null, $parentType)
    {

        $initialData = Bitrixfield::getForm($parentId, $parentType);
        $data = [
            'initial' => $initialData
        ];
        return APIController::getSuccess($data);
    }

    public static function store(Request $request)
    {

        $parent = null;
        $validatedData = $request->validate([
            'id' => 'sometimes|integer|exists:bitrixfields,id',
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
        // $fieldData = [
        //     'title' => $request['title'],
        //     'name' => $request['name'],
        //     'code' => $request['code'],
        //     'type' => $request['type'], //Тип филда (select, date, string)
        //     // 'entityType' => $request['entityType'],  // тип родителя - чтобы контроллер от этого условия определил нужную модель родителя
        //     'entity_id' => (int)$request['entity_id'],  // id сущности родителя, тип родителя определяется на сервере 

        //     'parent_type' => $request['parent_type'],   //принадлежность филда к родительской модели list complectField для доступа из родителя к определенного типа филдам в сделках - только для товаров например
        //     'bitrixId' => $request['bitrixId'],
        //     'bitrixCamelId' => $request['bitrixCamelId'],
        // ];

        if (isset($validatedData['id'])) {
            // Попытка найти существующее поле
            $field = Bitrixfield::find($validatedData['id']);
            if (!$field) {
                return APIController::getError('Bitrixfield not found', [], 404);
            }
        } else {
            // Создание нового поля, если ID не предоставлен
            $field = new Bitrixfield();
            if ($request['entityType'] == 'list') {
                $parent = Bitrixlist::class;
            } else   if ($request['entityType'] == 'smart') {
                $parent = Smart::class;
            } else   if ($request['entityType'] == 'deal') {
                $parent = BtxDeal::class;
            } else   if ($request['entityType'] == 'company') {
                $parent = BtxCompany::class;
            } else   if ($request['entityType'] == 'lead') {
                $parent = BtxLead::class;
            }

            // Заполняем или обновляем поля модели если модель создается
            $field->entity_type = $parent ?? null;
            $field->entity_id = $validatedData['entity_id'];
        }


        // Заполняем или обновляем поля модели

        $field->parent_type = $validatedData['parent_type'];
        $field->type = $validatedData['type'];
        $field->title = $validatedData['title'];
        $field->name = $validatedData['name'];
        $field->code = $validatedData['code'];
        $field->bitrixId = $validatedData['bitrixId'];
        $field->bitrixCamelId = $validatedData['bitrixCamelId'];

        $field->save();

        if ($field) {
            $resultBtxField = new BitrixFieldResource($field);

            return APIController::getSuccess([
                'bitrixfield' => $resultBtxField
            ]);
        }
        return APIController::getError('btx field was not created', [
            'bitrixfield' => $field,
            // 'fieldData' => $fieldData
        ]);
    }

    public static function get($bitrixfieldId)
    {
        $btxField = BitrixField::find($bitrixfieldId);
        if($btxField){
            $resultBtxField = new BitrixFieldResource($btxField);
            return APIController::getSuccess(['bitrixfield' => $resultBtxField]);
        }
        return APIController::getError('bitrixfield was not found', ['bitrixfieldId' => $bitrixfieldId]);
    }

    public static function delete($bitrixfieldId)
    {

        $btxField = BitrixField::find($bitrixfieldId);

        if ($btxField) {
            $btxField->delete();
            return APIController::getSuccess(['bitrixfield' => $btxField]);
        }
        return APIController::getError('btx field was not found and deleted', [
            'bitrixfield' => $btxField,
            // 'fieldData' => $fieldData
        ]);
    }
}
