<?php

namespace App\Http\Controllers;

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
            return APIController::getResponse(
                0,
                'success',
                ['rqs' => $rqs]
            );
        } catch (\Throwable $th) {
            return APIController::getResponse(
                1,
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
                return response([
                    'resultCode' => 1,
                    'rqId' => $rqId,
                    'message' => 'rq not found'
                ]);
            }

            return APIController::getResponse(
                0,
                'success',
                ['rq' => $rq]
            );
        } catch (\Throwable $th) {
            return APIController::getResponse(
                1,
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
}
