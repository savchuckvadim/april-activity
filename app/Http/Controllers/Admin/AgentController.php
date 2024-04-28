<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\APIController;
use App\Http\Controllers\Controller;
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
    
    public static function getProvider($providerId)
    {
        try {

            $provider = Agent::find($providerId);


            if (!$provider) {
                return response([
                    'resultCode' => 1,
                    'providerId' => $providerId,
                    'message' => 'provider not found'
                ]);
            }

            return APIController::getResponse(
                0,
                'success',
                ['provider' => $provider]
            );
        } catch (\Throwable $th) {
            return APIController::getResponse(
                1,
                $th->getMessage(),
                null
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

    public static function deleteProvider($providerId)
    {
        try {

            $provider = Agent::find($providerId);


            if (!$provider) {
                return response([
                    'resultCode' => 1,
                    'providerId' => $providerId,
                    'message' => 'provider not found'
                ]);
            }
            if ($provider) {
                $provider->delete();
            }
            return APIController::getResponse(
                0,
                'success' . $providerId . ' was deleted',
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
