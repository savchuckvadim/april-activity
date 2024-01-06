<?php

namespace App\Http\Controllers;

use App\Http\Resources\FieldCollection;
use App\Http\Resources\FieldResource;
use App\Models\Field;
use App\Models\FItem;
use App\Models\Template;
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


    public static function setField($templateId, $fieldData)
    {
        $resultField = null;

        try {
            $template = Template::find($templateId);

            if ($template) {

                $templateController = new TemplateController;
                $resultFields = $templateController->processFields([$fieldData], $template);
                if ($resultFields) {
                    $resultField = $resultFields[0];
                    return APIController::getSuccess([
                        'templateId' => $templateId,
                        'field' => $resultField,
                    ]);
                }
            } else {
                return APIController::getError(
                    'Template not found',
                    ['template' => $template]
                );
            }
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                null
            );
        }
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
        } else {
            $template = Template::find($templateId);
            if ($template) {
                $fields = $template->fields;
                if ($fields) {
                    $collectionFields = new FieldCollection($fields);
                    return APIController::getResponse(
                        0,
                        'success',
                        $collectionFields
                    );
                }
            }
        }

        return response([
            'resultCode' => 0,
            'isCollection' => true,
            'tfields' => $fields,

        ]);
    }
    public static function getAllFields()
    {
        $fields = [];



        $fields = Field::all();


        return response([
            'resultCode' => 0,
            'isCollection' => true,
            'fields' => $fields,

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

    public static function getInitialField()
    {

        $initialData = Field::getForm();
        $data = [
            'initial' => $initialData
        ];
        return APIController::getResponse(0, 'success', $data);
    }
}
