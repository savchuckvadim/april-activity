<?php

namespace App\Http\Resources;


use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;

class UserCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $this->except('updated_at', 'name', 'surname');
      
        $data = $this->collection->each(function ($item) {
            $currentUser = Auth::user();
            $id = $currentUser->id;
            if(!$item->photo){
                $item->photo = $item->getAvatarUrl();
                $item->save();
            }
           
            return $item;
        });
    
        return [
            'resultCode' => 1,
            'totalCount' =>  $this->collection->count(),
            'data' => $data,

        ];
    
    }
}