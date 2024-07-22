<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\APIController;
use App\Http\Controllers\Controller;
use App\Http\Resources\AgentResource;
use App\Models\Agent;
use App\Models\Portal;
use Illuminate\Http\Request;

class AgentController extends Controller
{

    public static function getInitial($portalId = null)
    {

        $initialData = Agent::getForm($portalId);
        $data = [
            'initial' => $initialData
        ];
        return APIController::getSuccess($data);
    }

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

            $resource = new AgentResource($provider);
            return APIController::getResponse(
                0,
                'success',
                ['provider' => $resource]
            );
        } catch (\Throwable $th) {
            return APIController::getResponse(
                1,
                $th->getMessage(),
                null
            );
        }
    }
    public static function getByPortal($portalId)
    {
        try {
            $result = [];
            $portal = Portal::find($portalId);
            $providers = $portal->providers;
            
            

            return APIController::getSuccess(
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
    public static function store(Request $request)
    {
        $id = null;
        $portal = null;
        $portalId = null;
        if (isset($request['portal_id'])) {
            $portalId =$request['portal_id'];
        }else if (isset($request['portal_id'])) {
            $portalId =$request['portalId'];
        }

        if (isset($request['id'])) {
            $id = $request['id'];
            $agent = Agent::find($id);
        } else {
            if (isset($portalId)) {

                $portalId = $portalId;
                $portal = Portal::find($portalId);
                $agent = new Agent();
                $agent->portalId = $portalId;
            }
        }
        $validatedData = $request->validate([
            'id' => 'sometimes|integer|exists:agents,id',
            'name' => 'required|string',
            'number' => 'required|string',
            'code' => 'required|string',
            'type' => 'required|string',
            // 'portal_id' => 'required|string',

        ]);

        if ($agent) {
            // Создание нового Counter
            $agent->name = $validatedData['name'];
            $agent->number = (string)$validatedData['number'];
            $agent->code = $validatedData['code'];
            $agent->type = $validatedData['type'];
            if(!empty($portalId)){
                $agent->portalId = $portalId;
            }
   


            $agent->save(); // Сохранение Counter в базе данных
            $resultagent = $agent;
            return APIController::getSuccess(
                ['provider' => $resultagent, 'portal' => $portal, '$portalId' => $portalId]
            );
        }

        return APIController::getError(
            'portal was not found',
            ['portal' => $portal]

        );
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


    public static function getSelectProviders($providerId = null)
    {
        $allproviders = [];
        $providerSelect = [];

        if ($providerId) {
            $int = intval($providerId);
            $findingprovider = Agent::find($int);
            if ($findingprovider) {
                $allproviders = [
                    $findingprovider
                ];
            }
        } else {
            $allproviders = Portal::all();
        }


        foreach ($allproviders  as $provider) {
            array_push($providerSelect, [
                'id' => $provider->id,
                'domain' => '$provider->domain',
                'name' => $provider->name,
                'title' => $provider->code,
            ]);
        };

        return  $providerSelect;
    }
}
