<?php

namespace App\Http\Controllers\Admin\Garant;

use App\Http\Controllers\APIController;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Garant\ComplectResource;
use App\Models\Garant\Complect;
use App\Models\Garant\Infoblock;
use Illuminate\Http\Request;

class ComplectController extends Controller
{
    public static function getInitial()
    {

        $initialData = Complect::getForm();
        $data = [
            'initial' => $initialData
        ];
        return APIController::getSuccess($data);
    }


    public static function store(Request $request)
    {

        try {
            //code...

            $request->merge([
                'withABS' => filter_var($request->input('withABS'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
                'withConsalting' => filter_var($request->input('withConsalting'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
                'withServices' => filter_var($request->input('withServices'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
                'withLt' => filter_var($request->input('withLt'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
                'isChanging' => filter_var($request->input('isChanging'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
                'withDefault' => filter_var($request->input('withDefault'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            ]);

            $validatedData = $request->validate([
                'id' => 'sometimes|integer|exists:complects,id',
                // 'entity_type' => 'required|string',
                'name' => 'required|string',
                'fullName' => 'required|string',
                'shortName' => 'required|string',
                'description' => 'sometimes|nullable|string',
                'code' => 'required|string',
                // 'code' => 'required|string',
                'type' => 'required|string',
                'color' => 'required|string',
                'weight' => 'required|string',
                'abs' => 'sometimes|nullable|string',
                'number' => 'required|string',
                'productType' => 'required|string',
                'withABS' => 'required|boolean',
                'withConsalting' => 'required|boolean',
                'withServices' => 'required|boolean',
                'withLt' => 'required|boolean',
                'isChanging' => 'required|boolean',
                'withDefault' => 'required|boolean',
            ]);
            $currentComplect =  null;
            if (!empty($validatedData['id'])) {
                $currentComplect = Complect::find($validatedData['id']);
            }

            if (empty($currentComplect)) {
                $currentComplect = new Complect($validatedData);
            }

            if (!empty($currentComplect)) {
                $currentComplect->save();
            }


            return APIController::getSuccess(
                ['complect' => $currentComplect]

            );
        } catch (\Throwable $th) {
            //throw $th;
            return APIController::getError(
                'complect was not updated',
                [$th->getMessage()]

            );
        }
    }
    public static function get($complectId)
    {
        try {
            $complect = Complect::find($complectId);

            if ($complect) {
                $complect = new ComplectResource($complect);
                return APIController::getSuccess(
                    ['complect' => $complect]
                );
            } else {
                return APIController::getError(
                    'complect was not found',
                    ['complect' => $complect]
                );
            }
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                ['complectId' => $complectId]
            );
        }
    }



    public static function getAll()
    {
        $complects = null;
        try {
            $complects = Complect::all();

            return APIController::getSuccess(
                ['complects' => $complects]

            );
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                ['complects' => $complects]
            );
        }
    }

    public static function infoblocks($complectId)
    {
        try {
            // Находим комплект
            $complect = Complect::with('infoblocks')->find($complectId);

            if (!$complect) {
                return APIController::getError('complect was not found', ['complectId' => $complectId]);
            }

            // Получаем **все** инфоблоки
            $infoblocks = Infoblock::all();

            // Получаем **связанные** инфоблоки (ID-шники)
            $linkedInfoblockIds = $complect->infoblocks->pluck('id')->toArray();

            // Формируем массив полей для фронта
            $iblockFields = [];
            foreach ($infoblocks as $key => $infoblock) {
                array_push(
                    $iblockFields,
                    [
                        'id' => $infoblock->id,
                        'title' => $infoblock->name,
                        'entityType' => 'complects',
                        'name' => $infoblock->code,
                        'apiName' => $infoblock->id,
                        'type' => 'boolean',
                        'validation' => 'required|max:255',
                        'initialValue' => in_array($infoblock->id, $linkedInfoblockIds), // ✅ Помечаем, связан ли инфоблок
                        'value' => in_array($infoblock->id, $linkedInfoblockIds) ? 'В комплекте' : '-', // ✅ Помечаем, связан ли инфоблок

                        'isCanAddField' => false,
                        'isRequired' => true, // хотя бы одно поле в шаблоне должно быть
                        'isLinked' => in_array($infoblock->id, $linkedInfoblockIds), // ✅ Помечаем, связан ли инфоблок
                    ]
                );
            }

            // Отправляем данные на фронт
            return APIController::getSuccess([
                'complect' => new ComplectResource($complect),
                'cinfoblocks' => $iblockFields,
            ]);
        } catch (\Throwable $th) {
            return APIController::getError($th->getMessage(), ['complectId' => $complectId]);
        }
    }

    public static function infoblock($infoblockId)
    {
        try {
            // Находим комплект


            // Получаем **все** инфоблоки
            $infoblock = Infoblock::find($infoblockId);
            if (!$infoblock) {
                return APIController::getError('infoblock was not found', ['infoblockId' => $infoblockId]);
            }
            // Получаем **связанные** инфоблоки (ID-шники)


            $infoblockForm =    [
                [
                    'id' => 1,
                    'title' => 'id',
                    'entityType' => 'complects',
                    'name' => 'fullName',
                    'apiName' => 'fullName',
                    'type' =>  'string',
                    'validation' => 'required|max:255',
                    'initialValue' => $infoblockId,
                    'isCanAddField' => false,
                    'isRequired' => true, //хотя бы одно поле в шаблоне должно быть

                ],
                [
                    'id' => 1,
                    'title' => 'В комплекте',
                    'entityType' => 'complects',
                    'name' => 'inComplect',
                    'apiName' => 'inComplect',
                    'type' =>  'boolean',
                    'validation' => 'required|max:255',
                    'initialValue' => '',
                    'isCanAddField' => false,
                    'isRequired' => true, //хотя бы одно поле в шаблоне должно быть

                ],

            ];



            // Отправляем данные на фронт
            return APIController::getSuccess([

                'cinfoblock' => $infoblockForm,
            ]);
        } catch (\Throwable $th) {
            return APIController::getError($th->getMessage(), ['infoblockId' => $infoblockId]);
        }
    }
    public static function initRelations($complectId)
    {
        try {
            // Находим комплект
            $complect = Complect::with('infoblocks')->find($complectId);

            if (!$complect) {
                return APIController::getError('complect was not found', ['complectId' => $complectId]);
            }

            // Получаем **все** инфоблоки
            $infoblocks = Infoblock::all();

            // Получаем **связанные** инфоблоки (ID-шники)
            $linkedInfoblockIds = $complect->infoblocks->pluck('id')->toArray();

            // Формируем массив полей для фронта
            $iblockFields = [];
            foreach ($infoblocks as $key => $infoblock) {
                array_push(
                    $iblockFields,
                    [
                        'id' => $infoblock->id,
                        'title' => $infoblock->name,
                        'entityType' => 'complects',
                        'name' => $infoblock->code,
                        'apiName' => $infoblock->id,
                        'type' => 'boolean',
                        'validation' => 'required|max:255',
                        'initialValue' => in_array($infoblock->id, $linkedInfoblockIds), // ✅ Помечаем, связан ли инфоблок
                        'value' => in_array($infoblock->id, $linkedInfoblockIds) ? 'В комплекте' : '-', // ✅ Помечаем, связан ли инфоблок

                        'isCanAddField' => false,
                        'isRequired' => true, // хотя бы одно поле в шаблоне должно быть
                        'isLinked' => in_array($infoblock->id, $linkedInfoblockIds), // ✅ Помечаем, связан ли инфоблок
                    ]
                );
            }
            $relation = [
                'apiName' => 'complects',
                'title' => 'Комплект Гарант',
                'entityType' => 'entity',
                'groups' => [
                    [
                        'groupName' => 'Комплект Гарант',
                        'entityType' => 'group',
                        'isCanAddField' => true,
                        'isCanDeleteField' => true,
                        'fields' =>  $iblockFields,

                        'relations' => [],

                    ]
                ]
            ];
            // Отправляем данные на фронт
            return APIController::getSuccess([
                'complect' => new ComplectResource($complect),
                'relation' => $relation,
            ]);
        } catch (\Throwable $th) {
            return APIController::getError($th->getMessage(), ['complectId' => $complectId]);
        }
    }
    // public static function getAll()
    // {

    //     // Создание нового Counter
    //     $smarts = Smart::all();
    //     if ($smarts) {

    //         return APIController::getSuccess(
    //             ['smarts' => $smarts]
    //         );
    //     }


    //     return APIController::getError(
    //         'callingGroups was not found',
    //         null

    //     );
    // }
}
