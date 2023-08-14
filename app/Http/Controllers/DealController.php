<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use Illuminate\Http\Request;

class DealController extends Controller
{
    public static function addDeal($request)
    {
        $deal = [
            'dealId' => $request->dealId,
            'userId' => $request->userId,
            'domain' => $request->domain,
            'dealName' => $request->dealName,
            'app' => $request->app,
            'global' => $request->global,
            'currentComplect' => $request->currentComplect,
            'od' => $request->od,
            'result' => $request->result,
            'contract' => $request->contract,
            'product' => $request->product,
            'rows' => $request->row
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
         
        } else{
            $resultDeal = $searchingDeal
        }
        return response([
            'resultCode' =>  $resultCode,
            'deal' => $resultDeal,
            'message' => $message
        ]);
    }
}
