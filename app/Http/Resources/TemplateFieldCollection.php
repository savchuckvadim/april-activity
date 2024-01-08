<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class TemplateFieldCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $this->except('updated_at');
        $this->except('created_at');
        $data = $this->collection->each(function ($item) {

            return new FieldResource($item);
        });

        // return [
        //     'totalCount' =>  $this->collection->count(),
        //     'data' => $data,

        // ];

        return $data;
        // return parent::toArray($request);
    }
}
