<?php

namespace App\Http\Controllers\Front\Konstructor;

use App\Http\Controllers\APIController;
use App\Http\Controllers\Controller;
use App\Models\OfferZakupkiSettings;
use App\Models\Portal;
use Illuminate\Http\Request;

class OfferZakupkiSettingsController extends Controller
{
    public function get($domain, $userId)
    {
        $favorites = [];
        try {
            $portal = Portal::where('domain', $domain)->first();
            $settings = OfferZakupkiSettings::where('domain', $domain)
                // ->where('userId', $userId)
                ->first();
        

            
            $result = [
                'settings' => $settings
            ];
            return APIController::getSuccess($result);
        } catch (\Throwable $th) {
            $errorMessages =  [
                'message'   => $th->getMessage(),
                'file'      => $th->getFile(),
                'line'      => $th->getLine(),
                'trace'     => $th->getTraceAsString(),
            ];

            return APIController::getError('zakupki settings get', [
                'error' =>  $errorMessages,
                'domain' => $domain,
                'userId' => $userId,

            ]);
        }
    }
}
