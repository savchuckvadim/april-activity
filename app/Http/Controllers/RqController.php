<?php

namespace App\Http\Controllers;

use App\Http\Resources\RqCollection;
use App\Http\Resources\RqResource;
use App\Models\Agent;
use App\Models\Rq;
use Bitrix24\Bizproc\Provider;
use Illuminate\Http\Request;

class RqController extends Controller
{
    public static function getRqs()
    {
        try {

            $rqs = Rq::all();
            $rqsCollection = new RqCollection($rqs);
            return APIController::getSuccess(
                $rqsCollection
            );
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                null
            );
        }
    }
    public static function getRq($rqId)
    {
        try {

            $rq = Rq::find($rqId);


            if (!$rq) {

                return APIController::getError(
                    'rq not found',
                    ['rqId' => $rqId]
                );
            }
            $rqResourse = new RqResource($rq);
            return APIController::getSuccess(
                ['rq' => $rqResourse]
            );
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                null
            );
        }
    }

    public static function setRqs($rqs)
    {
        $result = [];

        foreach ($rqs as $rqData) {
            $agentId = $rqData['agentId']; // Пример получения ID портала из данных поставщика
            $provider = Agent::where('number', $agentId)->first();

            // if ($provider) {


            $searchingAgent = Rq::updateOrCreate(
                ['number' => $rqData['number']], // Условие для поиска
                $rqData // Данные для обновления или создания
            );

            $result[] = $searchingAgent;
            // }
        }
        return response([
            'resultCode' => 0,
            'rqs' => $result
        ]);
    }


    public function update($entityType, $entityId, Request $request)
    {

        $rqData = [
            'number' => $request['number'],
            'name' => $request['name'],
            'type' => $request['type'],
            'fullname' => $request['fullname'],
            'shortname' => $request['shortname'],
            'director' => $request['director'],
            'position' => $request['position'],
            'accountant' => $request['accountant'],
            'based' => $request['based'],
            'inn' => $request['inn'],
            'kpp' => $request['kpp'],
            'ogrn' => $request['ogrn'],
            'ogrnip' => $request['ogrnip'],
            'personName' => $request['personName'],
            'document' => $request['document'],

            'docSer' => $request['docSer'],
            'docNum' => $request['docNum'],
            'docDate' => $request['docDate'],
            'docIssuedBy' => $request['docIssuedBy'],
            'docDepCode' => $request['docDepCode'],
            'registredAdress' => $request['registredAdress'],

            'registredAdress' => $request['registredAdress'],
            'primaryAdresss' => $request['primaryAdresss'],
            'email' => $request['email'],
            'garantEmail' => $request['garantEmail'],
            'phone' => $request['phone'],
            'assigned' => $request['assigned'],
            'assignedPhone' => $request['assignedPhone'],
            'other' => $request['other'],
            'bank' => $request['bank'],
            'bik' => $request['bik'],
            'rs' => $request['rs'],
            'ks' => $request['ks'],
            'bankAdress' => $request['bankAdress'],
            'bankOther' => $request['bankOther'],


        ];

        // Функция для замены 'null' строк на null значения
        $processData = function ($item) {
            return $item === 'null' ? null : $item;
        };

        // Обрабатываем каждый элемент массива
        $processedData = array_map($processData, $rqData);



        $rqModel = Rq::find($entityId);
        if ($rqModel) {
            $rqModel->update($processedData);
            return APIController::getSuccess([
                $entityType => $rqModel
            ]);
        } else {
            return APIController::getError($entityType . 'not found for update', $rqModel);
        }
    }

    public static function deleteRq($rqId)
    {
        try {

            $rq = Rq::find($rqId);



            if ($rq) {
                $rq->delete();
            }
            return APIController::getResponse(
                0,
                'success' . $rqId . ' was deleted',
                null
            );
        } catch (\Throwable $th) {
            return APIController::getResponse(
                1,
                $th->getMessage(),
                null
            );
        }
    }




    public static function getFiles($rqId, $childrenName)
    {

        try {
            if ($rqId && $childrenName) {
                switch ($childrenName) {
                    case 'logos':
                    case 'logo':
                        return RqController::getLogos($rqId);

                    case 'signatures':
                    case 'signature':
                        return RqController::getSignatures($rqId);

                    case 'stamps':
                    case 'stamp':
                        return RqController::getStamps($rqId);



                    default:
                        return APIController::getError(
                            'children name is undefined',
                            ['childrenName' => $childrenName]
                        );
                }
            }
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                ['rqId' => $rqId, 'childrenName' => $childrenName,]
            );
        }
    }

    public static function getLogos($rqId)
    {
        try {


            $rq = Rq::find($rqId);
            if ($rq) {
                $logos = $rq->logos;
                return APIController::getSuccess(['logos' => $logos]);
            } else {
                return APIController::getError(
                    'rq not found',
                    null
                );
            }
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                null
            );
        }
    }
    public static function getStamps($rqId)
    {
        try {


            $rq = Rq::find($rqId);
            if ($rq) {
                $stamps = $rq->stamps;
                return APIController::getSuccess(['stamps' => $stamps]);
            } else {
                return APIController::getError(
                    'rq not found',
                    null
                );
            }
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                null
            );
        }
    }
    public static function getSignatures($rqId)
    {
        try {


            $rq = Rq::find($rqId);
            if ($rq) {
                $signatures = $rq->signatures;
                return APIController::getSuccess(['signatures' => $signatures]);
            } else {
                return APIController::getError(
                    'rq not found',
                    null
                );
            }
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                null
            );
        }
    }
}
