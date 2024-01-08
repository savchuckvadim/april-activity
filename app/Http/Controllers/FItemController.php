<?php

namespace App\Http\Controllers;

use App\Models\Field;
use App\Models\FItem;
use Illuminate\Http\Request;

class FItemController extends Controller
{
    public static function getFitem($fitemId)
    {
        try {
            $fitem = FItem::find($fitemId);
            if ($fitem) {

                return APIController::getSuccess(
                    ['item' => $fitem]
                );
            } else {
                return APIController::getError(
                    'field was not found',
                    ['item' => $fitem]
                );
            }
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                ['item' => $fitem]
            );
        }
    }

    public static function getFitems($fieldId)
    {
        $fields = [];


        $field = Field::find($fieldId);
        if ($field) {
            $fitems = $field->items;
            if ($fitems) {
                // $collectionFields = new FieldCollection($fields);
                return APIController::getSuccess(
                    ['items' => $fitems]
                );
            }
        }else{
            return APIController::getError(
                'field was not found',
                ['field' => $field]
            );
        }


       
    }



    public static function getInitialFitem()
    {

        $initialData = FItem::getForm();
        $data = [
            'initial' => $initialData
        ];
        return APIController::getSuccess($data);
    }



    public static function setFitem($fieldId, $fitemData)
    {


        try {
            $field = Field::find($fieldId);
            if (!$fitemData['value']) {
                return APIController::getError('invalid data', ['$fitemData' => $fitemData]);
            }
            if ($field) {

                $fitemController = new FItemController;
                $fitems =  $fitemController->processFitems([$fitemData], $field);
                $newFitem = $fitems[0];
                if ($newFitem) {
                    return APIController::getSuccess([
                        'fieldId' => $fieldId,
                        'item' => $newFitem,
                    ]);
                } else {
                    return APIController::getError(
                        'fitem was not created',
                        ['fitemData' => $fitemData]
                    );
                }
            } else {
                return APIController::getError(
                    'Field not found',
                    ['field' => $field, 'fitemData' => $fitemData]
                );
            }
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                ['fitemData' => $fitemData]
            );
        }
    }


    public function processFitems(array $fitems, Field $field)
    {

        $result = [];
        foreach ($fitems as $fitemData) {

            if (!isset($fieldData['number'])) {
                $fitemData['number'] = 0;
            }
            if (!isset($fieldData['code'])) {
                $fitemData['code'] = 0;
            }
            if (!isset($fieldData['fieldNumber'])) {
                $fitemData['fieldNumber'] = $field->number;
            } 
            if (!isset($fieldData['fieldId'])) {
                $fitemData['fieldId'] = $field->id;
            }
            if (!isset($fieldData['order'])) {
                $fitemData['order'] = 0;
            }
            if (!isset($fieldData['bitrixId'])) {
                $fitemData['bitrixId'] = '';
            }


            $fitem = FItem::updateOrCreate($fitemData);

            // Связываем поле с шаблоном
            // $field->fitems()->attach($fitem->id);
            array_push($result, $fitem);
        }

        return $result;
    }
}
