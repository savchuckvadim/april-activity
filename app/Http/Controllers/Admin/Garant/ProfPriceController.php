<?php

namespace App\Http\Controllers\Admin\Garant;

use App\Http\Controllers\APIController;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Garant\ProfPriceResource;
use App\Models\Garant\GarantProfPrice;
use App\Models\Garant\Complect;
use App\Models\Garant\GarantPackage;
use App\Models\Garant\Supply;
use Illuminate\Http\Request;

class ProfPriceController extends Controller
{
    public static function getInitial()
    {
        $form = GarantProfPrice::getForm();
        $data = [
            'initial' => $form
        ];
        return APIController::getSuccess($data);
    }

    public static function store(Request $request)
    {
        $validated = $request->validate([
            'complect_id' => 'sometimes|nullable|exists:complects,id',
            'garant_package_id' => 'sometimes|nullable|exists:garant_packages,id',
            'supply_id' => 'sometimes|nullable|exists:supplies,id',
            'region_type' => 'required|in:msk,rgn',
            'supply_type' => 'nullable|string',
            'value' => 'required|numeric',
            'discount' => 'nullable|numeric',
        ]);

        $profPrice = GarantProfPrice::create($validated);

        return APIController::getSuccess(new ProfPriceResource($profPrice));
    }

    public static function get($profPriceId)
    {
        $profPrice = GarantProfPrice::findOrFail($profPriceId);
        return APIController::getSuccess(new ProfPriceResource($profPrice));
    }

    public static function getAll()
    {
        $profPrices = GarantProfPrice::all();
        return APIController::getSuccess(ProfPriceResource::collection($profPrices));
    }

    public static function complects($profPriceId)
    {
        $profPrice = GarantProfPrice::findOrFail($profPriceId);
        return APIController::getSuccess($profPrice->complect);
    }

    public static function garantPackages($profPriceId)
    {
        $profPrice = GarantProfPrice::findOrFail($profPriceId);
        return APIController::getSuccess($profPrice->garantPackage);
    }

    public static function supplies($profPriceId)
    {
        $profPrice = GarantProfPrice::findOrFail($profPriceId);
        return APIController::getSuccess($profPrice->supply);
    }

    public static function initRelations($profPriceId)
    {
        $profPrice = GarantProfPrice::findOrFail($profPriceId);
        $complects = Complect::all();
        $garantPackages = GarantPackage::all();
        $supplies = Supply::all();

        return APIController::getSuccess([
            'complects' => $complects,
            'garantPackages' => $garantPackages,
            'supplies' => $supplies,
        ]);
    }

    public static function storeRelations(Request $request, int $profPriceId)
    {
        $profPrice = GarantProfPrice::findOrFail($profPriceId);

        $validated = $request->validate([
            'complect_id' => 'required|exists:complects,id',
            'garant_package_id' => 'required|exists:garant_packages,id',
            'supply_id' => 'required|exists:supplies,id',
        ]);

        $profPrice->update($validated);

        return APIController::getSuccess(new ProfPriceResource($profPrice));
    }
}
