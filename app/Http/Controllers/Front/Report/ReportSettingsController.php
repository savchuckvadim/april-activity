<?php

namespace App\Http\Controllers\Front\Report;

use App\Http\Controllers\APIController;
use App\Http\Controllers\Controller;
use App\Models\Portal;
use App\Models\Report\ReportSettings;
use Exception;
use Illuminate\Http\Request;

class ReportSettingsController extends Controller
{
    public function get(Request $request)
    {
        $domain = $request->domain;
        $userId = $request->userId;
        $result = [
            'filter' => []
        ];
        try {
            $portal = Portal::where('domain', $domain)->first();
            $settings = ReportSettings::where('portalId', $portal->id)
                ->where('bxUserId', $userId)
                ->first();

            if ($settings) {
                if (!empty($settings->filter)) {
                    $resultSettings = json_decode($settings->filter, true);
                    $result['filter'] = $resultSettings;
                }
            }

            return APIController::getSuccess($result);
        } catch (\Throwable $th) {
            $errorMessages =  [
                'message'   => $th->getMessage(),
                'file'      => $th->getFile(),
                'line'      => $th->getLine(),
                'trace'     => $th->getTraceAsString(),
            ];

            return APIController::getError('report settings get', [
                'error' =>  $errorMessages,
                'domain' => $domain,
                'userId' => $userId,
                'filter' => []

            ]);
        }
    }


    public function store(Request $request)
    {
        $resultDeal = null;
        try {
            $filter = $request->filter;
            $domain = $request->domain;
            $userId = $request->userId;
            $portal = Portal::where('domain', $domain)->first();

            if ($portal) {


                $settings = [
                    'domain' => $domain,
                    // 'consalting' => $request->consalting,
                    'portalId' => $portal->id,
                    'bxUserId' => $userId,


                    'filter' => $request->filter,


                ];





                $searchingSettings = ReportSettings::where('portalId', $portal->id)
                    ->where('bxUserId', $userId)
                    ->first();



                if (!empty($searchingSettings)) {
                    $searchingSettings->update($settings);
                    $searchingSettings->save();
                    $resultSettings =  $searchingSettings;
                } else {
                    //search portal

                    $newSettings = new ReportSettings($settings);
                    $newSettings->save();
                    $newSettings = ReportSettings::where('portalId', $portal->id)
                        ->where('bxUserId', $userId)
                        ->first();
                    $resultSettings = $newSettings;
                }
            }else{
                throw new Exception('portal was not found');
            }
            return APIController::getSuccess([
                'filter' => $filter,
                'settings' => $resultSettings


            ]);
        } catch (\Throwable $th) {
            $errorMessages =  [
                'message'   => $th->getMessage(),
                'file'      => $th->getFile(),
                'line'      => $th->getLine(),
                'trace'     => $th->getTraceAsString(),
            ];

            return APIController::getError('favorite store', [
                'error' =>  $errorMessages,
                'data' => $request->all(),

            ]);
        }
    }
}
