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
                ->where('bxUserId', $userId)
                ->first();

            if (!$settings) {
                $settings = OfferZakupkiSettings::where('domain', $domain)
                    ->first();
            }
            if (!$settings) {
                $settings = $this->createDefaultSettings($domain);
            }
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

    protected function createDefaultSettings($domain)
    {
        $portal = Portal::where('domain', $domain)->first();
        $data = array(
            'portal_id' => $portal->id,
            'domain' => $domain,
            'name' => 'Настройки по умолчанию',
            'provider1_letter_text' => 'Имеем честь предложить заключить контракт на период {{period}} на следующий комплект: ',

            'provider2_letter_text' => 'Имеем честь предложить заключить контракт на период с  {{period}}  года на следующий комплект: ',
            'is_default' => true,

        );
        $settings = new OfferZakupkiSettings($data);
        $settings->save();
        return $settings;
    }
}
