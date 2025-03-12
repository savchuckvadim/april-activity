<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\APIController;
use App\Http\Controllers\Controller;
use App\Models\Garant\Infoblock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InfoblockController extends Controller
{
    public static function getInitial()
    {

        $initialData = Infoblock::getForm();
        $data = [
            'initial' => $initialData
        ];
        return APIController::getSuccess($data);
    }

    public static function getInfoblock($infoblockId)
    {
        try {

            $infoblock = Infoblock::find($infoblockId);


            if (!$infoblock) {
                return response([
                    'resultCode' => 1,
                    'infoblockId' => $infoblockId,
                    'message' => 'infoblock not found'
                ]);
            }

            return APIController::getResponse(
                0,
                'success',
                ['infoblock' => $infoblock]
            );
        } catch (\Throwable $th) {
            return APIController::getResponse(
                1,
                $th->getMessage(),
                null
            );
        }
    }
    public static function setInfoBlocks($infoblocks)
    {
        $result = [];
        $resultCode = 1;
        $message = 'something wrong with infogroups online';




        foreach ($infoblocks as $block) {

            $newBlock = Infoblock::where('code', $block['code'])->first();
            if (!$newBlock) {
                $newBlock = new Infoblock();
            }

            $newBlock['number'] = $block['number'];

            $newBlock['name'] = $block['name'];
            $newBlock['title'] = $block['title'];
            $newBlock['description'] = $block['description'];
            $newBlock['descriptionForSale'] = $block['descriptionForSale'];
            $newBlock['shortDescription'] = $block['shortDescription'];
            $newBlock['weight'] = $block['weight'];

            $newBlock['code'] = $block['code'];
            $newBlock['inGroupId'] = $block['inGroupId'];
            $newBlock['groupId'] = $block['groupId'];
            $newBlock['isLa'] = $block['isLa'];
            $newBlock['isFree'] = $block['isFree'];
            $newBlock['isShowing'] = $block['isShowing'];
            $newBlock['isSet'] = $block['isSet'];

            $newBlock->save();
            array_push($result, $newBlock);
        }



        if (count($result) > 0) {
            $resultCode = 0;
            $message = null;
        }



        return response([
            'resultCode' => $resultCode,
            'data' => $result,
            'message' => $message
        ]);
    }
    public static function updateInfoblock($infoblockId, Request $request) //update by id or fail
    {


        try {
            // Преобразуем строковые значения в булевые перед валидацией
            $request->merge([
                'isLa' => in_array($request->input('isLa'), ['true', '1', 1, true], true),
                'isFree' => in_array($request->input('isFree'), ['true', '1', 1, true], true),
                'isShowing' => in_array($request->input('isShowing'), ['true', '1', 1, true], true),
                'isSet' => in_array($request->input('isSet'), ['true', '1', 1, true], true),
                'isProduct' => in_array($request->input('isProduct'), ['true', '1', 1, true], true),
                'isPackage' => in_array($request->input('isPackage'), ['true', '1', 1, true], true),
            ]);
            // Валидация данных
            $validatedData = Validator::make($request->all(), [
                'number' => 'required|integer',
                'name' => 'required|string',
                'title' => 'nullable|string',
                'description' => 'nullable|string',
                'descriptionForSale' => 'nullable|string',
                'shortDescription' => 'nullable|string',
                'weight' => 'required|string',
                'code' => 'required|string',
                'isLa' => 'required|boolean',
                'isFree' => 'required|boolean',
                'isShowing' => 'required|boolean',
                'isSet' => 'required|boolean',
                'isProduct' => 'nullable|boolean',
                'isPackage' => 'nullable|boolean',
                //
                'inGroupId' => 'required|numeric',
                'groupId' => 'required|numeric',
            ]);

            if ($validatedData->fails()) {
                return APIController::getError('Validation failed', $validatedData->errors());
            }

            $infoblock = Infoblock::find($infoblockId);

            if ($infoblock) {
                $data = $validatedData->validated();

                // Обработка значения 'null' для поля title
                if ($request->input('title') === 'null') {
                    $data['title'] = null;
                }

                $infoblock->update($data);

                return APIController::getSuccess(['infoblock' => $infoblock]);
            } else {
                return APIController::getError('Infoblock was not found', ['infoblockId' => $infoblockId]);
            }
        } catch (\Throwable $th) {
            return APIController::getError(
                'something wrong ' . $th->getMessage(),
                ['infoblockId' => $infoblockId]
            );
        }
    }

    public static function setInfoblock(Request $request)  //update by nuber or create
    {
        try {
            if (isset($request['number'])) {
                $block = $request->all();

                $isLa = false;
                $isFree = false;
                $isShowing = false;
                $isSet = false;

                if (isset($request['isLa'])) {
                    if ($request['isLa'] == 'true' || $request['isLa'] == '1') {
                        $isLa = true;
                    }
                }
                if (isset($request['isFree'])) {
                    if ($request['isFree'] == 'true' || $request['isFree'] == '1') {
                        $isFree = true;
                    }
                }
                if (isset($request['isShowing'])) {
                    if ($request['isShowing'] == 'true' || $request['isShowing'] == '1') {
                        $isShowing = true;
                    }
                }
                if (isset($request['isSet'])) {
                    if ($request['isSet'] == 'true' || $request['isSet'] == '1') {
                        $isSet = true;
                    }
                }

                $data = [
                    'number' => $block['number'],
                    'name' => $block['name'],
                    'title' => $block['title'],
                    'description' => $block['description'],
                    'descriptionForSale' => $block['descriptionForSale'],
                    'shortDescription' => $block['shortDescription'],
                    'weight' => $block['weight'],
                    'code' => $block['code'],
                    'inGroupId' => $block['inGroupId'],
                    'groupId' => $block['groupId'],
                    'isLa' => $isLa,
                    'isFree' => $isFree,
                    'isShowing' => $isShowing,
                    'isSet' => $isSet,
                ];

                $infoblockNumber = $block['number'];
                $infoblock = Infoblock::updateOrCreate(
                    ['number' => $infoblockNumber], // Поиск по ID
                    $data
                );
                return APIController::getSuccess(['infoblock' => $infoblock]);
            } else {
                return APIController::getError('invalid number data', ['data' => $request->all()]);
            }
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                ['data' => $request->all()]
            );
        }
    }
    public static function getInfoblocksDescription($parts)
    {
        $result = [];
        foreach ($parts as $key => $part) {
            // array_push($result, $key);
            $result[$key] = [];
            foreach ($part as $group) {

                $updatedGroup = [
                    'groupsName' => $group['groupsName'],
                    'value' => []

                ];

                foreach ($group['value'] as $infoblock) {

                    if (isset($infoblock['code'])) {
                        $searchingCode = $infoblock['code'];
                        $bd_infoblock = Infoblock::where('code', $searchingCode)->first();
                        array_push($updatedGroup['value'], $bd_infoblock);
                    } else {
                        array_push($updatedGroup['value'], $infoblock);
                    }
                };

                array_push($result[$key], $updatedGroup);
            };
            // array_push($result,  $part);
        };
        // $bd_infoblock = Infoblock::all();
        // array_push($result, $bd_infoblock);

        return response([
            'resultCode' => 0,
            'infoblocks' => $result
        ]);
    }

    public static function initRelations($infoblockId)
    {
        try {
            // Находим InfoGroup
            $infoblock = Infoblock::with('inPackage')->find($infoblockId);

            if (!$infoblock) {
                return APIController::getError('infoblock was not found', ['infoblockId' => $infoblockId]);
            }

            // Получаем **все** инфоблоки
            $infoblocks = Infoblock::all();

            // Получаем **связанные** инфоблоки (ID-шники)
            $inPackageIds = $infoblock->inPackage->pluck('id')->toArray();

            // Формируем массив полей для фронта инфоблоки в пакете
            $inPackageFields = $infoblocks->map(function ($block) use ($inPackageIds) {
                $isLinked = in_array($block->id, $inPackageIds);
                return [
                    'id' => $block->id,
                    'title' => $block->name,
                    'entityType' => 'inPackage',
                    'name' => $block->code,
                    'apiName' => $block->id,
                    'type' => 'boolean',
                    'validation' => 'required|max:255',
                    'initialValue' => $isLinked,  // Отмечаем, связан ли инфоблок
                    'value' => $isLinked,
                    'isCanAddField' => false,
                    'isRequired' => true,
                    'isLinked' => $isLinked
                ];
            });
         
            $relation = [
                'apiName' => 'infoblock',
                'title' => 'инфоблок',
                'entityType' => 'entity',
                'groups' => [
                    [
                        'groupName' => 'инфоблок в пакете',
                        'apiName' => 'inPackage',
                        'entityType' => 'group',
                        'isCanAddField' => true,
                        'isCanDeleteField' => true,
                        'fields' =>  $inPackageFields,

                        'relations' => [],

                    ]
                ]
            ];
            // Отправляем данные на фронт
            return APIController::getSuccess([
                'infoblock' =>  $infoblock,
                'relation' => $relation,
            ]);
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                ['infoblockId' => $infoblockId]
            );
        }
    }

    public function storeRelations(Request $request, int $infoblockId)
    {
        try {
            // Находим Infoblock
            $infoblock = Infoblock::with('inPackage')->find($infoblockId);

            if (!$infoblock) {
                return APIController::getError('infoblock was not found', ['infoblockId' => $infoblockId]);
            }
            $relationGroups = $request->groups;
            $relationInfoblock = [];
            foreach ($relationGroups as $group) {
                if ($group['apiName'] == 'inPackage') {
                    $attachIds = [];
                    foreach ($group['fields'] as $field) {
                        if ($field['value']) {
                            array_push($relationInfoblock, $field);
                            $attachIds[] = $field['id'];
                        }
                    }

                    // Сохраняем связи (удаляет старые и добавляет новые)
                    $infoblock->inPackage()->sync($attachIds);
                }
            }


            $infoblock = Infoblock::with('inPackage')->find($infoblockId);

            return APIController::getSuccess([
                'result' => [
                    'infoblockId' => $infoblockId,
                    'relationInfoblock' => $relationInfoblock,
                    'inPackage' =>  $infoblock->inPackage
                ]
            ]);
        } catch (\Throwable $th) {
            return APIController::getError($th->getMessage(), ['infoblockId' => $infoblockId]);
        }
    }
}
