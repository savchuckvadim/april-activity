<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class OfferCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // $collection = [];
        // foreach ($this->collection as $offer) {
        //     if ($offer->advertiser) {
        //         array_push($collection, $offer);
        //     }
        // }

        return [
            'resultCode' => 1,
            'totalCount' =>  $this->collection->count(),
            'offers' => $this->collection,

        ];
    }
}
