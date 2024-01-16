<?php

namespace App\Http\Controllers;

use App\Http\Resources\FieldCollection;
use App\Http\Resources\FieldResource;
use App\Models\Field;
use App\Models\FItem;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

                $fieldController = new FieldController;
                $resultFields = $fieldController->processFields([$fieldData], $template);
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
                    'type' => $request->input('type') == 'null' ? '' : $request->input('type'),
                    'code' => $request->input('code') == 'null' ? '' : $request->input('code'),
                    'value' => $request->input('value') == 'null' ? '' : $request->input('value'),
                    'description' => $request->input('description') == 'null' ? '' : $request->input('description'),
                    'bitixId' => $request->input('bitixId') == 'null' ? '' : $request->input('bitixId'),
                    'bitrixTemplateId' => $request->input('bitrixTemplateId') == 'null' ? '' : $request->input('bitrixTemplateId'),
                    'isGeneral' => ($request->input('isGeneral') == 'true' || $request->input('isGeneral') == '1' || $request->input('isGeneral') == 1) ? 1 : 0,
                    'isDefault' => ($request->input('isDefault') == 'true' || $request->input('isDefault') == '1' || $request->input('isDefault') == 1) ? 1 : 0,
                    'isRequired' => ($request->input('isRequired') == 'true' || $request->input('isRequired') == '1' || $request->input('isRequired') == 1) ? 1 : 0,
                    'isActive' => ($request->input('isActive') == 'true' || $request->input('isActive') == '1' || $request->input('isActive') == 1) ? 1 : 0,
                    'isPlural' => ($request->input('isPlural') == 'true' || $request->input('isPlural') == '1' || $request->input('isPlural') == 1) ? 1 : 0,
                    'isClient' => ($request->input('isClient') == 'true' || $request->input('isClient') == '1' || $request->input('isClient') == 1) ? 1 : 0,
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


    //UTILS
    public function processFields(array $fields, Template $template)
    {
        try {
            $result = [];
            foreach ($fields as $fieldData) {
                $fieldData['type'] = $fieldData['type'] == 'null' ? '' : $fieldData['type'];
                $fieldData['code'] = $fieldData['code'] == 'null' ? '' : $fieldData['code'];
                $fieldData['value'] = $fieldData['value'] == 'null' ? '' : $fieldData['value'];
                $fieldData['description'] = $fieldData['description'] == 'null' ? '' : $fieldData['description'];
                $fieldData['bitixId'] = $fieldData['bitixId'] == 'null' ? '' : $fieldData['bitixId'];
                $fieldData['bitrixTemplateId'] = $fieldData['bitrixTemplateId'] == 'null' ? '' : $fieldData['bitrixTemplateId'];

                $fieldData['isGeneral'] = $fieldData['isGeneral'] == 'true' ? 1 : 0;
                $fieldData['isDefault'] = $fieldData['isDefault'] == 'true' ? 1 : 0;
                $fieldData['isRequired'] = $fieldData['isRequired'] == 'true' ? 1 : 0;
                $fieldData['isActive'] = $fieldData['isActive'] == 'true' ? 1 : 0;
                $fieldData['isPlural'] = $fieldData['isPlural'] == 'true' ? 1 : 0;
                $fieldData['isClient'] = $fieldData['isClient'] == 'true' ? 1 : 0;

                // if (!isset($fieldData['isGeneral'])) {
                //     $fieldData['isGeneral'] = false;
                // }

                // if (!isset($fieldData['isDefault'])) {
                //     $fieldData['isDefault'] = false;
                // }
                // if (!isset($fieldData['isRequired'])) {
                //     $fieldData['isRequired'] = false;
                // }
                // if (!isset($fieldData['isActive'])) {
                //     $fieldData['isActive'] = false;
                // }
                // if (!isset($fieldData['isPlural'])) {
                //     $fieldData['isPlural'] = false;
                // }
                if (!isset($fieldData['type'])) {
                    $fieldData['type'] = 'string';
                }

                $field = $this->createOrUpdateField($fieldData);

                // Связываем поле с шаблоном
                $template->fields()->attach($field->id);
                array_push($result, $field);
            }

            return $result;
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    protected function createOrUpdateField(array $fieldData)
    {
        $field = new Field();

        // Заполнение полей модели Field данными
        // Замените это на соответствующий код заполнения
        foreach ($fieldData as $key => $value) {
            if ($key !== 'img') {
                $field->$key = $value;
            }
        }

        // Если это поле с изображением, сохраняем файл и устанавливаем initialValue
        if (isset($fieldData['img']) && $fieldData['img'] instanceof UploadedFile) {
            $filePath = $fieldData['img']->store('public/template/images/test');
            $field->value = Storage::url($filePath);
        }
        $field->number = 0;
        $field->code = 'field.' . Str::uuid()->toString();
        $field->save();

        return $field;
    }
}
