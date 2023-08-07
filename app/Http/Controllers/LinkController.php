<?php

namespace App\Http\Controllers;

use App\Models\Link;
use App\Models\Offer;
use Illuminate\Support\Facades\Auth;


class LinkController extends Controller
{
    public static function create($offerId)
    {
        $authUserId = Auth::user()->id;
        $offer = Offer::find($offerId);
        if ($offer) {
            $advertiserId = $offer->advertiser->id;

            $checkLink = Link::where('offer_id', $offerId)  //проверяем не сущестует ли уже ссылка такая
                ->where('master_id', $authUserId)
                ->first();
            if ($checkLink) {
                return response([
                    'resultCode' => 0,
                    'message' => 'Link is already created!'

                ]);
            } else {
                $link = new Link();
                $link->advertiser_id = $advertiserId;
                $link->master_id = $authUserId;
                $link->offer_id = $offerId;
                $link->transitions = 0;

                $link->url = url('/link');
                $link->save();
                $hashId = $link->id;
                $link->url = url("/link/{$hashId}");
                $link->save();
                return response([
                    'resultCode' => 1,
                    'link' => $link->url

                ]);
            }
        } else {
            return response([
                'resultCode' => 0,
                'message' => 'Offer not found!'

            ]);
        }
    }
    public static function urlForRedirect($linkId)
    {
        $link = Link::find($linkId);
        if(!$link){
            return getenv('SPA_URL').'/notfound';
        }
        $offer = $link->offer;
        $followers = $offer->followers;
        $follower = $followers->find($link->master_id);
        $advertiser = $offer->advertiser;
        if ($follower && $advertiser) {
            $link->transitions += 1;
            $link->save();
            return $offer->url;
        } else {
            $link->fail_transitions += 1;
            $link->save();
            return getenv('SPA_URL').'/notfound';
        }
    }
}
