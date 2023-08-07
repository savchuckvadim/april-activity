<?php

namespace App\Http\Controllers;

use App\Http\Resources\OfferResource;
use App\Models\Offer;
use App\Models\OfferMaster;
use Illuminate\Support\Facades\Auth;

class OfferMasterController extends Controller
{
    public static function follow($request){
       
        $currentUserId =  Auth::user()->id;
        $offerId = $request->offerId;
        $findFollow = OfferMaster::where('master_id', $currentUserId)->where('offer_id', $offerId)->first();
        if(!$findFollow ){
            $follow = new OfferMaster();
            $follow->master_id = $currentUserId;
            $follow->offer_id = $offerId;
            $follow->save();
            $offer = Offer::find($offerId);
            $resource = new OfferResource($offer);
    
            return response([
                'resultCode' => 1,
                'changedOffer' => $resource,
                '$findFollow '=> $findFollow 
    
            ]);
        }else{
            return response([
                'resultCode' => 0,
                'message' => 'it\'s allready following',
                '$findFollow '=> $findFollow 
    
            ]);
        }
       
    }

    public static function unfollow($offerId){
       
        $currentUserId =  Auth::user()->id;
        
        $findFollow = OfferMaster::where('master_id', $currentUserId)->where('offer_id', $offerId)->first();
        if($findFollow ){
            $findFollow->delete();

            return response([
                'resultCode' => 1,
                'message' => 'is deleted',
                
    
            ]);
        }else{
            return response([
                'resultCode' => 0,
                'message' => 'not found',
                
    
            ]);
        }
       
    }

    //TODO: delete user...
}
