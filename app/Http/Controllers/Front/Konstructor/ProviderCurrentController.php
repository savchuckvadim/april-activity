<?php

namespace App\Http\Controllers\Front\Konstructor;

use App\Http\Controllers\APIController;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProviderCollection;
use App\Models\Konstructor\ProviderCurrent;
use App\Models\Portal;
use Illuminate\Http\Request;

class ProviderCurrentController extends Controller
{
    public function get(Request $request)
    {
        $domain = null;
        $userId = null;

        try {
            $domain = $request->domain;
            $userId = $request->userId;
            $provider = ProviderCurrent::where('domain', $domain)
                ->where('bxUserId', $userId)
                ->first();

            return APIController::getSuccess(['provider' => $provider]);
        } catch (\Throwable $th) {
            $errorMessages =  [
                'message'   => $th->getMessage(),
                'file'      => $th->getFile(),
                'line'      => $th->getLine(),
                'trace'     => $th->getTraceAsString(),
            ];

            return APIController::getError('provider get', [
                'error' =>  $errorMessages,
                'domain' => $domain,
                'userId' => $userId,

            ]);
        }
    }

    public function store(Request $request)
    {
        $domain = null;
        $userId = null;
        $portalId = null;

        try {
            $domain = $request->domain;
            $userId = $request->userId;

            $portal = Portal::where('domain', $request->domain)->first();

            if ($portal) {
                $portalId = $portal->id;
            }


            $provider = ProviderCurrent::updateOrCreate(
                ['domain' => $request->domain, 'bxUserId' => $request->userId],
                [
                    'portalId' => $portalId,
                    'agentId' => $request->agentId,
                    'name' => $request->providerName
                ]
            );

            return APIController::getSuccess(['provider' => $provider]);
        } catch (\Throwable $th) {
            $errorMessages =  [
                'message'   => $th->getMessage(),
                'file'      => $th->getFile(),
                'line'      => $th->getLine(),
                'trace'     => $th->getTraceAsString(),
            ];

            return APIController::getError('provider store', [
                'error' =>  $errorMessages,
                'domain' => $domain,
                'userId' => $userId,

            ]);
        }
    }

    public function providers(Request $request)
    {
        $domain = null;
        $userId = null;

        try {
            $domain = $request->domain;
            $userId = $request->userId;
            $provider = ProviderCurrent::where('domain', $domain)
                ->where('bxUserId', $userId)
                ->first();
            $portal = Portal::where('domain', $domain)
                ->first();
            $providers =  $portal->providers;
            $providersCollection = new ProviderCollection($providers);
            return APIController::getSuccess(['result' => ['current' => $provider, 'providers' => $providersCollection]]);
            
        } catch (\Throwable $th) {
            $errorMessages =  [
                'message'   => $th->getMessage(),
                'file'      => $th->getFile(),
                'line'      => $th->getLine(),
                'trace'     => $th->getTraceAsString(),
            ];

            return APIController::getError('provider get', [
                'error' =>  $errorMessages,
                'domain' => $domain,
                'userId' => $userId,

            ]);
        }
    }
}
