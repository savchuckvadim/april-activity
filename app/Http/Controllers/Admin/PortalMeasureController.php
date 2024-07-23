<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\APIController;
use App\Http\Controllers\Controller;
use App\Models\Measure;
use App\Models\Portal;
use App\Models\PortalMeasure;
use Illuminate\Http\Request;

class PortalMeasureController extends Controller
{
    public static function getInitial($portalId = null)
    {

        $initialData = PortalMeasure::getForm($portalId);
        $data = [
            'initial' => $initialData
        ];
        return APIController::getSuccess($data);
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $id = null;
        $portal = null;
        $measure = null;

        if (isset($request['id'])) {
            $id = $request['id'];
            $portalMeasure = Measure::find($id);
        } else {
            $portalMeasure = new PortalMeasure();
            if (isset($request['portal_id'])) {
                $portal_id = $request['portal_id'];

                $portalMeasure->portal_id = $portal_id;
                $portal = Portal::find($portal_id);
            }

            if (isset($request['measure_id'])) {

                $measure_id = $request['measure_id'];
                $portalMeasure->measure_id = $measure_id;

                $measure = Portal::find($measure_id);
            }
        }
        $validatedData = $request->validate([
            'id' => 'sometimes|integer|exists:portal_measure,id',
            'bitrixId' => 'required|string',
            'name' => 'sometimes',
            'shortName' => 'sometimes',
            'fullName' => 'sometimes',



        ]);


        if ($portalMeasure) {
            // Создание нового Counter


            $portalMeasure->name = (string)$validatedData['name'];
            $portalMeasure->shortName = (string)$validatedData['shortName'];
            $portalMeasure->fullName = (string)$validatedData['fullName'];
            $portalMeasure->bitrixId = $validatedData['bitrixId'];

            $portalMeasure->save(); // Сохранение Counter в базе данных

            return APIController::getSuccess(
                ['portalmeasure' => $portalMeasure, 'portal' => $portal, 'measure' => $measure]
            );
        }

        return APIController::getError(
            'portalmeasure was not found',
            ['rq' => $request]

        );
    }

    /**
     * Display the specified resource.
     */
    public function get($portalMeasureId)
    {
        try {
            $portalMeasure = PortalMeasure::find($portalMeasureId);

            if ($portalMeasure) {
                // $resultSmart = new SmartResource($rpa);
                return APIController::getSuccess(
                    ['portalmeasure' => $portalMeasure]
                );
            } else {
                return APIController::getError(
                    'portalMeasure was not found',
                    ['portalmeasure' => $portalMeasure]
                );
            }
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                ['portalMeasureId' => $portalMeasureId]
            );
        }
    }

    public  function getAll()
    {

        // Создание нового Counter
        $measures = Measure::all();
        if ($measures) {

            return APIController::getSuccess(
                ['portalmeasures' => $measures]
            );
        }


        return APIController::getError(
            'portalmeasures was not found',
            null

        );
    }



    public function getByPortal($portalId)
    {

        // Создание нового Counter
        $portal = Portal::find($portalId);
        $measures = $portal->measures;
        if ($measures) {

            return APIController::getSuccess(
                ['portalmeasures' => $measures]
            );
        }


        return APIController::getError(
            'portalmeasures was not found',
            ['portal id' => $portalId]

        );
    }
    /**
     * Show the form for editing the specified resource.
     */

    public function destroy($measureId)
    {
        $measure = PortalMeasure::find($measureId);

        if ($measure) {
            // Получаем все связанные поля
            $measure->delete();
            return APIController::getSuccess($measure);
        } else {

            return APIController::getError(
                'portalmeasure not found',
                null
            );
        }
    }
}
