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
                if ($resultFields && is_array($resultFields)) {
                    $resultField = $resultFields[0];
                    return APIController::getSuccess([
                        'templateId' => $templateId,
                        'field' => $resultField,
                    ]);
                } else {
                    return APIController::getError(
                        'Field was not creating',
                        [
                            'resultFields' => $resultFields,
                            'template' => $template,
                            'fieldData' => $fieldData
                        ]
                    );
                }
            } else {
                return APIController::getError(
                    'Template not found',
                    ['template' => $template, 'fieldData' => $fieldData]
                );
            }
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                null
            );
        }
    }


    public static function getField($fieldId)
    {
        try {
            $field = Field::find($fieldId);
            if ($field) {
                $fieldResource = new FieldResource($field);
                return APIController::getSuccess(
                    ['field' => $fieldResource]
                );
            } else {
                return APIController::getError(
                    'field was not found',
                    ['field' => $field]
                );
            }
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                ['fieldId' => $fieldId]
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
                    return APIController::getSuccess(
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
        return APIController::getSuccess($data);
    }

    public function updateField($fieldId, Request $request)
    {

        try {
            $field = Field::find($fieldId);
            if ($field) {
                $field->update([
                    'number' => $request['number'],
                    'name' => $request['name'],
                    'type' => $request['type'],
                    'code' => $request['code'],
                    'value' => $request['value'],
                    'description' => $request['description'],
                    'bitixId' => $request['bitixId'],
                    'bitrixTemplateId' => $request['bitrixTemplateId'],
                    'isGeneral' => $request['isGeneral'],
                    'isDefault' => $request['isDefault'],
                    'isRequired' => $request['isRequired'],
                    'isActive' => $request['isActive'],
                    'isPlural' => $request['isPlural'],
                    'isClient' => $request->input('isClient') == 'true' ? 1 : 0,
                ]);
                $responseData = [
                    'field' => $field,

                ];
                return APIController::getSuccess($responseData);
            } else {
                $responseData = [
                    'fieldId' => $fieldId,
                    'data' => $request,

                ];
                return APIController::getError('something wrong with save field', $responseData);
            }
        } catch (\Throwable $th) {
            $message = $th->getMessage();
            return APIController::getError('something wrong with save field: ' . $message, null);
        }
    }


    public static function deleteField($fieldId)
    {
        $field = Field::find($fieldId);

        if ($field) {
            // Получаем все связанные поля
            $field->delete();
            return APIController::getSuccess($field);
        } else {
            // Код для случая, когда шаблон не найден...
            return response([
                'resultCode' => 1,
                'message' => 'Template not found',
            ]);
        }
    }
}
