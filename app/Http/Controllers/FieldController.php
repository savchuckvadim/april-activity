<?php

namespace App\Http\Controllers;

use App\Http\Resources\FieldCollection;
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
            'tfields' => $resultFields
        ]);
    }

    public static function getFields($templateId)
    {
        $fields = [];

        if ($templateId == 'all' || $templateId == null) {

            $fields = Field::all();
        } else  if ($templateId == 'general') {

            $fields = Field::where('isGeneral', true)->get();
            $generalFieldsCollection = new FieldCollection($fields);
            $fields = $generalFieldsCollection;
        }

        return response([
            'resultCode' => 0,
            'isCollection' => true,
            'tfields' => $fields,
            '$templateId' => $templateId
        ]);
    }


    public static function createField(
        $templateId,
        $field
    ) {
        //СОЗДАТЬ  FIELD И ЕГО FITEMS
        //СВЯЗАТЬ С FITEMS И TEMPLATE
        // $name,
        // $type,
        // $isGeneral,
        // $isRequired,
        // $value,
        // $description,
        // $bitixId,
        // $bitrixTemplateId,
        // $isActive,
        // $isPlural
        return response([
            'templateId' => $templateId,
            'field' => $field
        ]);
    }

    public static function getDataForCreateField()
    {


        $data = [

            [
                'name' => 'name',
                'type' => 'string',
                'value' => null,
                'items' => [],

            ],
            [
                'name' => 'type',
                'type' => 'string',
                'value' => null,
                'items' => []
            ],


            [
                'name' => 'value',
                'type' => 'string',
                'value' => null,
                'items' => []
            ],
            [
                'name' => 'description',
                'type' => 'string',
                'value' => null,
                'items' => []
            ],

            [
                'name' => 'bitrixId',
                'type' => 'string',
                'value' => null,
                'items' => []
            ],
            [
                'name' => 'bitrixTemplateId',
                'type' => 'string',
                'value' => null,
                'items' => []
            ],
            [
                'name' => 'isActive',
                'type' => 'boolean',
                'value' => null,
                'items' => []
            ],
            [
                'name' => 'isPlural',
                'type' => 'string',
                'value' => null,
                'items' => []
            ],
            [
                'name' => 'isGeneral',
                'type' => 'boolean',
                'value' => null,
                'items' => []
            ],

            [
                'name' => 'isRequired',
                'type' => 'boolean',
                'value' => null,
                'items' => []
            ],

        ];


        return response([
            'resultCode' => 0,
            'initialField' =>  $data
        ]);
    }
}
