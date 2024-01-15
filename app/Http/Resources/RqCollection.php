<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class RqCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // return parent::toArray($request);
        $this->except('updated_at');
        $this->except('created_at');

        $data = $this->collection->each(function ($item) {

            return new RqResource($item);
        });

        return [
            'totalCount' =>  $this->collection->count(),
            'rqs' => $data,

        ];
    }
}
