<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\APIController;
use App\Http\Controllers\Controller;
use App\Models\Measure;
use App\Models\Portal;
use Illuminate\Http\Request;

class MeasureController extends Controller
{
    public static function getInitial($portalId = null)
    {

        $initialData = Measure::getForm($portalId);
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
        if (isset($request['id'])) {
            $id = $request['id'];
            $measure = Measure::find($id);
        } else {
            // if (isset($request['portal_id'])) {

                $measure = new Measure();
            // }
        }
        $validatedData = $request->validate([
            'id' => 'sometimes|integer|exists:measures,id',
            // 'entity_type' => 'required|string',
            'name' => 'required|string',
      
            'shortName' => 'required|string',
            'fullName' => 'required|string',
            'code' => 'required|string',
            'type' => 'required|string',

  
        ]);
        $type = $validatedData['type'];
        $code = $validatedData['code'];
        $name = $validatedData['name'];
        $shortName = $validatedData['shortName'];
        $fullName = $validatedData['fullName'];
      

        if ($measure) {
            // Создание нового Counter


            $measure->name = $name;
            $measure->shortName = $shortName;
            $measure->type = $type;
            $measure->code = $code;

            $measure->fullName = $fullName;
 
            $measure->save(); // Сохранение Counter в базе данных

            return APIController::getSuccess(
                ['measure' => $measure, 'portal' => $portal]
            );
        }

        return APIController::getError(
            'portal was not found',
            ['portal' => $portal]

        );
    }

    /**
     * Display the specified resource.
     */
    public function get($measureId)
    {
        try {
            $measure = Measure::find($measureId);

            if ($measure) {
                // $resultSmart = new SmartResource($rpa);
                return APIController::getSuccess(
                    ['measure' => $measure]
                );
            } else {
                return APIController::getError(
                    'measure was not found',
                    ['measure' => $measure]
                );
            }
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                ['measureId' => $measureId]
            );
        }
    }

    public  function getAll()
    {

        // Создание нового Counter
        $measures = Measure::all();
        if ($measures) {

            return APIController::getSuccess(
                ['measures' => $measures]
            );
        }


        return APIController::getError(
            'measures was not found',
            null

        );
    }


    public function destroy($btxRpaId)
    {
        $measure = Measure::find($btxRpaId);

        if ($measure) {
            // Получаем все связанные поля
            $measure->delete();
            return APIController::getSuccess($measure);
        } else {

            return APIController::getError(
                'measure not found',
                null
            );
        }
    }
}
