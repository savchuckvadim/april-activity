<?php

namespace App\Http\Controllers;

use App\Models\Infoblock;
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
            if (isset($block['number'])) {
                $block = $request;
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

                $infoblockNumber = $block['number'];
                $infoblock = Infoblock::updateOrCreate(
                    ['number' => $infoblockNumber], // Поиск по ID
                    $data
                );
                return APIController::getSuccess(['infoblock' => $infoblock]);
            } else {
                return APIController::getError('invalid number data', ['data' => $request]);
            }
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                ['data' => $request]
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
}
