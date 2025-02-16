<?php

namespace App\Http\Controllers\Front\Konstructor;

use App\Http\Controllers\APIController;
use App\Http\Controllers\Controller;
use App\Models\DealDocumentOption;
use App\Models\Portal;
use Illuminate\Http\Request;

class DealDocumentOptionController extends Controller
{
    public static function priceOptionstore(Request $request)
    {
        try {
            $domain = $request->domain;
            $portal = Portal::where('domain', $domain)->firstOrFail();

            $validatedData = $request->validate([
                'bxUserId' => 'required|numeric',
                'dealDocumentFavoriteId' => 'sometimes|nullable|string', // Теперь учитываем favoriteId
                'priceSupply' => 'required|nullable|string',
                'priceDefault' => 'required|string',
                'priceDiscount' => 'required|string',
                'priceYear' => 'required|string',
                'priceOptions' => 'required|string',
            ]);

            $validatedData['portalId'] = $portal->id;

            $priceOptions = DealDocumentOption::findOrCreateOptions($validatedData, $portal);

            return APIController::getSuccess(['priceOptions' => $priceOptions]);
        } catch (\Throwable $th) {
            return APIController::getError(
                'priceOptions update failed',
                [
                    'message' => $th->getMessage(),
                    'file' => $th->getFile(),
                    'line' => $th->getLine(),
                    'trace' => $th->getTraceAsString(),
                ]
            );
        }
    }


    public static function infoblockOptionstore(Request $request)
    {
        try {
            $domain = $request->domain;
            $portal = Portal::where('domain', $domain)->firstOrFail();

            $validatedData = $request->validate([
                'bxUserId' => 'required|numeric',
                'dealDocumentFavoriteId' => 'sometimes|nullable|string', // Теперь учитываем favoriteId
                'salePhrase' => 'required|nullable|string',
                'withStamp' => 'required|string',
                'isPriceFirst' => 'required|string',
                'withManager' => 'required|string',
                'iblocksStyle' => 'required|string',
                'describStyle' => 'required|string',
            ]);

            $validatedData['portalId'] = $portal->id;

            $infoblockOptions = DealDocumentOption::findOrCreateOptions($validatedData, $portal);

            return APIController::getSuccess(['infoblockOptions' => $infoblockOptions]);
        } catch (\Throwable $th) {
            return APIController::getError(
                'infoblockOptions update failed',
                [
                    'message' => $th->getMessage(),
                    'file' => $th->getFile(),
                    'line' => $th->getLine(),
                    'trace' => $th->getTraceAsString(),
                ]
            );
        }
    }

    public static function getOptions(Request $request)
    {
        try {
            $domain = $request->domain;
            $portal = Portal::where('domain', $domain)->firstOrFail();

            $validatedData = $request->validate([
                'bxUserId' => 'required|numeric',
                'dealDocumentFavoriteId' => 'sometimes|nullable|string', // Теперь можно искать по favoriteId
            ]);

            $validatedData['portalId'] = $portal->id;

            $options = DealDocumentOption::getOptions($validatedData, $portal);

            if (!$options) {
                return APIController::getError('options not found ' . $domain, $validatedData);
            }

            return APIController::getSuccess(['options' => $options]);
        } catch (\Throwable $th) {
            return APIController::getError(
                'Failed to get options '. $domain,
                [
                    'message' => $th->getMessage(),
                    'file' => $th->getFile(),
                    'line' => $th->getLine(),
                    'trace' => $th->getTraceAsString(),
                ]
            );
        }
    }
}
