<?php

namespace App\Http\Controllers\Front\Report;

use App\Http\Controllers\APIController;
use App\Http\Controllers\Controller;
use App\Models\Portal;
use App\Models\Report\ReportSettings;
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

            $settings = [
                'domain' => $request->app,
                // 'consalting' => $request->consalting,
                'portalId' => $request->contract,
                'bxUserId' => $request->currentComplect,


                'filter' => $request->dealName,


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
                    // $newSettings->save();
                    $resultSettings = $newSettings;
                
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
