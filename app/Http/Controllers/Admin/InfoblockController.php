<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\APIController;
use App\Http\Controllers\Controller;
use App\Models\Garant\Infoblock;
use Illuminate\Http\Request;

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
            $infoblock = Infoblock::find($infoblockId);

            if ($infoblock) {
                $block = $request;
                $title = $block['title'];
                if ($block['title'] === 'null') {
                    $title = null;
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
                    'isLa' => $block['isLa'],
                    'isFree' => $block['isFree'],
                    'isShowing' => $block['isShowing'],
                    'isSet' => $block['isSet'],
                ];


                $infoblock->update($data);
                return APIController::getSuccess(['infoblock' => $infoblock]);
            } else {

                return APIController::getError('infoblock was not found', ['infoblockId' => $infoblockId]);
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
            $inPackageFields = [];
            foreach ($infoblocks as $key => $infoblock) {
                array_push(
                    $inPackageFields,
                    [
                        'id' => $infoblock->id,
                        'title' => $infoblock->name,
                        'entityType' => 'inPackage',
                        'name' => $infoblock->code,
                        'apiName' => $infoblock->id,
                        'type' => 'boolean',
                        'validation' => 'required|max:255',
                        'initialValue' => in_array($infoblock->id, $inPackageIds), // ✅ Помечаем, связан ли инфоблок
                        'value' => in_array($infoblock->id, $inPackageIds), // ✅ Помечаем, связан ли инфоблок

                        'isCanAddField' => false,
                        'isRequired' => true, // хотя бы одно поле в шаблоне должно быть
                        'isLinked' => in_array($infoblock->id, $inPackageIds), // ✅ Помечаем, связан ли инфоблок
                    ]
                );
            }
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
                    foreach ($group['fields'] as $field) {
                        $childrenInfoblock = Infoblock::find($field['id']);

                        if ($field['value']) {

                            array_push($relationInfoblock, $field);
                            // Привязка дочернего инфоблока к пакету
                            $childrenInfoblock->parentPackage()->associate($infoblock);
                            $childrenInfoblock->save();
                        } else {
                            // Удаление дочернего инфоблока из пакета
                            $childrenInfoblock->parentPackage()->dissociate();
                            $childrenInfoblock->save();
                        }
                    }
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
