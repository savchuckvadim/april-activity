<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\Portal;
use Illuminate\Http\Request;

class DealController extends Controller
{
    public static function addDeal($request)
    {

        //todo get or create portal
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
        $resultDeal = null;
        $resultCode = 1;
        $message = 'something wrong with saving deal';

        //search portal
        $searchingPortal = null;

        //search deal
        $searchingDeal = Deal::where('dealId', $request->dealId)
            ->where('domain', $request->domain)
            ->first();

        if ($searchingDeal) {
            // $searchingDeal->update($deal);
            // $searchingDeal->save();
            $resultDeal =  $searchingDeal;
        } else {
            //search portal
            $searchingPortal = Portal::where('domain', $request->domain)
                ->first();
            if ($searchingPortal) {
                $newDeal = new Deal([...$deal, 'portalId' => $searchingPortal->id]);
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
    }

    public static function getDeal($request)
    {

        // $request -> dealId  domain

        $resultDeal = null;
        $resultCode = 0;
        $message = '';

        $searchingDeal = Deal::where('dealId', $request->dealId)
            ->where('domain', $request->domain)
            ->first();


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
