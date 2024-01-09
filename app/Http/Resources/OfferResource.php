<?php

namespace App\Http\Resources;


use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class OfferResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

      
            $authUser = Auth::user();
            $isFollowing = 0;

            $link = null;
            $url = null;

            $followers = $this->followers;
            $followersCount = $followers->count();
            $linksCount = $this->links->count();


            if ($authUser->role_id === 1 || $authUser->role_id === 2) {
                $url = $this->url;
            } else if ($authUser->role_id === 3) { //if Master

                if ($followers->count()) {
                    $finIsFollow = $followers->find($authUser->id);
                    if ($finIsFollow) {
                        $isFollowing = 1;
                    }
                }
                if ($linksCount) {
                    $link = $this->links->where('master_id', $authUser->id)
                        ->where('offer_id', $this->id)->first();
                    if ($link) {
                        $link = $link->url;
                    }
                }
            }



            return [
                'id' => $this->id,
                'name' => $this->name,
                'description' => $this->description,
                'url' => $url,
                'price' => $this->price,
                'mastersProfit' => $this->mastersProfit,
                'followers' => $followersCount,
                'advertiser' => $this->advertiser,
                'created_at' => $this->created_at,
                'isFollowing' => $isFollowing,
                'links' => $linksCount,
                'link' => $link,
                'transitions' => $this->transitions()
            ];
      
    }

    public function with($request)
    {
        return [
            'resultCode' => 1,
        ];
    }
}
