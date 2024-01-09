<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class FieldCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request = null)
    {
        $this->except('updated_at');
        $this->except('created_at');
        $data = $this->collection->map(function ($item) {

            return new FieldResource($item);
        });

        // return [
        //     'totalCount' =>  $this->collection->count(),
        //     'data' => $data,

        // ];

        return [
            'totalCount' =>  $this->collection->count(),
            'fields' => $data,

        ];
        // return parent::toArray($request);
    }
}
