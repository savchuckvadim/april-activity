<?php

namespace App\Http\Controllers;

use App\Http\Resources\FieldResource;
use App\Models\Field;
use App\Models\FItem;
use Illuminate\Http\Request;

class FieldController extends Controller
{
    public static function setFields($fields, $items)
    {
        $result = [];

        foreach ($fields as $field) {


            $updatingField = Field::updateOrCreate(
                ['number' => $field['number']], // Условие для поиска
                $field // Данные для обновления или создания
            );
            $updatingField->save();

            if ($field['type'] === 'array') {

                foreach ($items as $item) {

                    if ($item['fieldNumber'] === $field['number']) {
                        $item['fieldId'] = $updatingField->id;
                        $searchingItems = FItem::updateOrCreate(
                            ['code' => $item['code']], // Условие для поиска
                            $item // Данные для обновления или создания,

                        );

                        $searchingItems->save();
                    }
                }
            }
        }

        $fields = Field::with('items')->get();
        $resultFields = [];
        foreach ($fields as $field) {
            $resField = new FieldResource($field);
            array_push($resultFields, $resField);
        }


        return response([
            'resultCode' => 0,
            'fields' => $resultFields
        ]);
    }

    public static function getFields($templateId)
    {
        $fields = [];

        if ($templateId == 'all') {

            $fields = Field::all();
        } else  if($templateId == 'general') {

            $fields = Field::where('isGeneral', true)->get();
        } 

        return response([
            'resultCode' => 0,
            'fields' => $fields,
            '$templateId' => $templateId
        ]);
    }

  
}
