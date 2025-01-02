<?php

namespace App\Http\Controllers;

use App\Models\BxDocumentDeal;
use App\Models\Deal;
use App\Models\Portal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DealController extends Controller
{
    public static function addDeal($request)
    {
        try {
            $deal = [
                'app' => $request->app,
                // 'consalting' => $request->consalting,
                'contract' => $request->contract,
                'currentComplect' => $request->currentComplect,

                'dealId' => $request->dealId,
                'dealName' => $request->dealName,
                'domain' => $request->domain,


                'global' => $request->global,
                // 'legalTech' => $request->legalTech,
                'od' => $request->od,
                'portalId' => $request->portalId,
                'result' => $request->result,
                'rows' => $request->rows,

                'userId' => $request->userId,

                // 'product' => $request->product,

            ];
            if (isset($request->regions)) {
                $deal['regions'] = $request->regions;
            }

            $resultDeal = null;
            $resultCode = 1;
            $message = 'something wrong with saving deal';

            //search portal
            $searchingPortal = null;

            $searchingDeal = BxDocumentDeal::where('dealId', $request->dealId)
                ->where('domain', $request->domain)
                ->first();

            // if (empty($searchingDeal)) {
            //     //search deal
            //     $searchingDeal = Deal::where('dealId', $request->dealId)
            //         ->where('domain', $request->domain)
            //         ->first();
            // }


            if (!empty($searchingDeal)) {
                $searchingDeal->update($deal);
                $searchingDeal->save();
                $resultDeal =  $searchingDeal;
            } else {
                //search portal
                $searchingPortal = Portal::where('domain', $request->domain)
                    ->first();
                if ($searchingPortal) {
                    $newDeal = new BxDocumentDeal([...$deal, 'portalId' => $searchingPortal->id]);
                    $newDeal->save();
                    $resultDeal = $newDeal;
                }
            }


            if ($resultDeal) {
                $resultCode = 0;
                $message = '';
            }

            return response([
                'resultCode' =>  $resultCode,
                'deal' => $resultDeal,
                'message' => $message,
                'searchingDeal' => $searchingDeal
            ]);
        } catch (\Throwable $th) {
            $errorMessages =  [
                'message'   => $th->getMessage(),
                'file'      => $th->getFile(),
                'line'      => $th->getLine(),
                'trace'     => $th->getTraceAsString(),
            ];
            Log::channel('telegram')->error('APRIL_ONLINE', [
                'DealController.addDeal' => [
                    'message' => $message,
                    $errorMessages

                ]
            ]);

            Log::error('APRIL_ONLINE', [
                'DealController.addDeal' => [
                    'message' => $message,

                ]
            ]);
            return response([
                'resultCode' =>  $resultCode,
                'deal' => $resultDeal,
                'message' => $message,
                'searchingDeal' => $searchingDeal
            ]);
        }

        //todo get or create portal

    }

    public static function copy($request)
    {
        $existingDeal = null;
        try {
            $currentDealId = $request->input('dealId');
            $newDealId = $request->input('newDealId');
            $userId = $request->input('userId');
            // Ищем существующую сделку
            // $existingDeal = Deal::where('dealId', $currentDealId)->first();
            $existingDeal = BxDocumentDeal::where('dealId', $request->currentDealId)
                ->where('domain', $request->domain)
                ->first();

            if (empty($existingDeal)) {
                //search deal
                $existingDeal = Deal::where('dealId', $request->currentDealId)
                    ->where('domain', $request->domain)
                    ->first();
            }
            if (empty($existingDeal)) {
                throw new \Exception('Deal not found');
            }

            // Создаем копию сделки с новым dealId
            $newDeal = $existingDeal->replicate();
            $newDeal->dealId = $newDealId;
            $newDeal->userId = $userId;
            $newDeal->department = 'sales';
            $newDeal->save();


    

            return APIController::getSuccess([
                'deal' => $newDeal,
                'oldDeal' => $existingDeal
            ]);
        } catch (\Throwable $th) {
            $message = $th->getMessage();
            $errorMessages =  [
                'message'   => $th->getMessage(),
                'file'      => $th->getFile(),
                'line'      => $th->getLine(),
                'trace'     => $th->getTraceAsString(),
            ];
       
            return APIController::getError($message, [
                'info' => $errorMessages,
                'searchingDeal' => $existingDeal
            ]);
        }
    }

    public static function getDeal($request)
    {

        // $request -> dealId  domain

        $resultDeal = null;
        $resultCode = 0;
        $message = '';

        $searchingDeal = BxDocumentDeal::where('dealId', $request->dealId)
            ->where('domain', $request->domain)
            ->first();

        if (empty($searchingDeal)) {
            $searchingDeal = Deal::where('dealId', $request->dealId)
                ->where('domain', $request->domain)
                ->first();
        }


        if (!$searchingDeal) {
            $resultCode = 1;
            $message = 'deal was not found';
        } else {
            $resultDeal = $searchingDeal;
        }
        return response([
            'resultCode' =>  $resultCode,
            'deal' => $resultDeal,
            'message' => $message
        ]);
    }

    public static function getDeals($parameter, $value)
    {

        // $request -> dealId  domain

        $resultDeal = null;
        $resultCode = 0;
        $message = '';

        $searchingDeal = Deal::where($parameter, $value)
            ->get();


        if (!$searchingDeal) {
            $resultCode = 1;
            $message = 'deals was not found';
        } else {
            $resultDeal = $searchingDeal;
        }
        return response([
            'resultCode' =>  $resultCode,
            'deals' => $resultDeal,
            'message' => $message
        ]);
    }
}
