<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Portal;
use Illuminate\Http\Request;

class AgentController extends Controller
{

    public static function getProviders()
    {
        try {
            $result = [];

            $providers = Agent::all();


            return APIController::getResponse(
                0,
                'success',
                ['providers' => $providers]
            );
        } catch (\Throwable $th) {
            return APIController::getResponse(
                1,
                $th->getMessage(),
                ['providers' => null]
            );
        }
    }

    public static function setProviders($providers)
    {
        $result = [];

        foreach ($providers as $providerData) {
            $domain = $providerData['portal']; // Пример получения ID портала из данных поставщика
            $portal = Portal::where('domain', $domain)->first();

            if ($portal) {




                $providerData['portalId'] = $portal->id; // Пример присвоения ID портала

                $searchingAgent = Agent::updateOrCreate(
                    ['number' => $providerData['number']], // Условие для поиска
                    $providerData // Данные для обновления или создания
                );

                $result[] = $searchingAgent;
            }
        }
        return response([
            'resultCode' => 0,
            'providers' => $result
        ]);
    }
}
