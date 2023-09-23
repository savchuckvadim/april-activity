<?php

namespace App\Http\Controllers;

use App\Models\Deal;
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

        $searchingDeal = Deal::where('dealId', $request->dealId)
            ->where('domain', $request->domain)
            ->first();

        if ($searchingDeal) {
            $searchingDeal->update($deal);
            $searchingDeal->save();
            $resultDeal =  $searchingDeal;
        } else {
            $newDeal = new Deal($deal);
            $newDeal->save();
            $resultDeal = $newDeal;
        }


        if ($resultDeal) {
            $resultCode = 0;
            $message = '';
        }

        return response([
            'resultCode' =>  $resultCode,
            'deal' => $resultDeal,
            'message' => $message
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
}
