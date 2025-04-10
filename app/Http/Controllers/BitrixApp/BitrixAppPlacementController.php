<?php

namespace App\Http\Controllers\BitrixApp;

use App\Http\Controllers\APIController;
use App\Http\Controllers\Controller;
use App\Models\Bitrix\BitrixApp;
use Illuminate\Http\Request;

class BitrixAppPlacementController extends Controller
{
    public function store(Request $request)
    {
        $domain = null;
        try {
            $data = $request->validate([
                'domain' => 'required|string',
                'code' => 'required|string',
                'placements' => 'required|array',
                'placements.*.code' => 'required|string',
                'placements.*.type' => 'required|string',
                'placements.*.group' => 'required|string',
                'placements.*.status' => 'required|string',
                'placements.*.bitrix_heandler' => 'required|string',
                'placements.*.public_heandler' => 'required|string',
                'placements.*.bitrix_codes' => 'required|string',
            ]);

            $bitrixApp = BitrixApp::whereHas('portal', function ($q) use ($data) {
                $q->where('domain', $data['domain']);
            })->where('code', $data['code'])->firstOrFail();

            // $bitrixApp->placements()->delete(); // можно очистить старые
            $bitrixApp->placements()->createMany($data['placements']);

            // return response()->json(['message' => 'Placements saved']);
            return APIController::getSuccess([
                'message' => 'Bitrix App Placement saved',
                'app_id' => $bitrixApp->id,
                'app' => $bitrixApp,
                'domain' => $domain,
            ]);
        } catch (\Throwable $th) {
            $errorMessages =  [
                'message'   => $th->getMessage(),
                'file'      => $th->getFile(),
                'line'      => $th->getLine(),
                'trace'     => $th->getTraceAsString(),
            ];

            return APIController::getError('Bitrix App Placement failed', [
                'domain' => $domain,
                'request' => $request,
                'details' => $errorMessages,
            ]);
        }
    }
}
